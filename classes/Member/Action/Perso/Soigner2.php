<?php
/** Soigner un personnage (blessures superficielles): But soigner le perso
*
* @package Member_Action
*/

//Quelques trucs à vérifier, au niveau des ID, cheats etc

class Member_Action_Perso_Soigner2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?popup=1&amp;m=Action_Perso_Soigner';
		
		
		$pvSuplementaires=0.75;//1pa soigne 0.75pv
		$facteurResistance = 3; //1 résitance (trousse) soigne 3 pv
		
		
		//Valider l'état du perso
		if(!$perso->isConscient())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
		
		
		//Valider si un item à été sélectionné
		if(!isset($_POST['itemId']))
			return fctErrorMSG('Aucune trousse n\'a été sélectionnée', $errorUrl);
		
		
		//Valider si un personnage à été sélectionné
		if(!isset($_POST['blesse']))
			return fctErrorMSG('Aucun personnage à soigner sélectionné', $errorUrl);		
		
		
		
		$itemId = $_POST['itemId'];		
		
		
		//Recherche du perso à soigner
		$i=0;
		while($arrPerso = $perso->getLieu()->getPerso($perso, $i++))
		{
			if(($arrPerso->getId() == $_POST['blesse']))
			{
				$blesse = $arrPerso;
				break;
			}
		}
		
		//Valider si le personnage à soigner est présent dans le lieu actuel
		if(!isset($blesse))
			return fctErrorMSG('Perso non soignable ou n\'étant pas dans votre lieu. (déplacé ou soigné entre temps)', $errorUrl);	
		
		
		//Valicer si le personnage peut être soigné vu son état
		if($blesse->getCoeffSoinNecessaire() > 0)
			return fctErrorMSG('Soins impossibles sur cette personne vu son état', $errorUrl);
		
		
		//Si aucun item n'a été sélection (soin de base): Valider si la personne a déjà recu les soins de base
		if(($blesse->getSoin()) && ($itemId == 0))
			return fctErrorMSG('Cette personne a déjà reçu les soins de base.', $errorUrl);	
				
		
		
		if($itemId != 0) //Si une trousse à été sélectionnée, la rechercher
		{	
		
			$pa = $_POST[$itemId];//Nombre de PAs saisis par l'utilisateur
		
				
			//récup de la trousse
			$i=0;
			while( $item = $perso->getInventaire($i++))
			{
				if($item instanceof Member_ItemTrousse)
				{
					if($item->getInvId() == $_POST['itemId'])
					{
						if($item->getResistance() > 0)
						{
							$trousse = $item;
							break;
						}
					}
				}	
			}
			
			//Valider si la trousse existe
			if(!isset($trousse))
				return fctErrorMSG('Cheat: Trousse non conforme ou ne vous appartenant pas.', $errorUrl);	
			
			
			//Valider si le nombre de PA est numérique
			if(!is_numeric($pa))
				return fctErrorMSG('Nombre de PA non numérique.', $errorUrl);
		}
		else //Soin manuel de base
		{
			$nullItemArray = array(
								'db_regrouper'=>'0',
								'db_nom'=>'Poings',
								'db_desc'=>'',
								'db_img'=>NULL,
								'db_pr'=>0,
								'db_notemj'=>'',
								'inv_notemj'=>'',
								'inv_qte'=>0,
								'inv_equip'=>'0',
								'inv_id'=>0,
								'db_id'=>0,
								'inv_persoid'=>$perso->getId(),
								'inv_lieutech'=>NULL,
								'inv_boutiquelieutech'=>NULL,
								'inv_boutiquePrixVente'=>NULL,
								'inv_boutiquePrixAchat'=>NULL,
								'db_valeur'=>0,
								'inv_cacheno'=>NULL,
								'inv_cachetaux'=>NULL,
								'db_resistance'=>1,
								'inv_resistance'=>1
							);
			$pa = 5;
			$trousse = new Member_ItemTrousse($nullItemArray);
		}


		
		//Valider si le perso possède assez de PA pour effectuer l'action
		if($perso->getPa()<=$pa)
			return fctErrorMSG('Vous n\'avez pas assez de PA pour effectuer cette action.', $errorUrl);	
		
		
		
		$reussiteScrs =  $perso->getChancesReussite("SCRS");
		$compteurPV = 0;
		$pafinal = 0;
		
		if($itemId != 0) //Si on soigne avec une trousse
		{
			//Tant que le nombre de PA à dépensé n'est pas dépensé, que le perso n'est pas soigné et qu'il reste de la résistance à la trousse
			for($i=0;
				($i<$pa)
					&& ($blesse->getPv() + round($compteurPV) < $blesse->getPvMax())
					&& (round( round($compteurPV) / $facteurResistance) < $trousse->getResistance());
				$i++)
			{
				if(rand(1,100) < $reussiteScrs)
					$compteurPV += $pvSuplementaires;
				$pafinal++;
			}
		}
		else //Si on soigne avec les moyens du bord
		{
			//Tant que le nombre de PA à dépensé n'est pas dépensé, que le perso n'est pas soigné 
			for($i=0;
				($i<$pa)
					&& ($blesse->getPv() + round($compteurPV) < $blesse->getPvMax());
				$i++)
			{
				if((rand(1,100) < $reussiteScrs))
					$compteurPV += $pvSuplementaires;
				$pafinal++;
			}
		}
		
		$compteurPV = round($compteurPV);
		
		if($itemId != 0)
		{
			$resistanceRetiree = round( $compteurPV / $facteurResistance);
			$resistanceMaj = $trousse->getResistance() - $resistanceRetiree;
			
			$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
				. ' SET inv_resistance=:resistance'
				. ' WHERE inv_id=:itemId'
				. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':resistance',	$resistanceMaj,			PDO::PARAM_INT);
			$prep->bindValue(':itemId',		$trousse->getInvId(),	PDO::PARAM_INT);
			$prep->executePlus($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
		else
		{
			$query = 'UPDATE ' . DB_PREFIX . 'perso'
				. ' SET soin=1'
				. ' WHERE id=:persoId'
				. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId',	$blesse->getId(),	PDO::PARAM_INT);
			$prep->executePlus($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			$resistanceRetiree = "'Soins de base'";
		}
		
		
		if(DEBUG_MODE)
			echo "<br />Le taux de réussite était de: ". (100-$reussiteScrs) ."% , ".$pafinal." PA on été consommés, ".$compteurPV." PV ont été remis, ".$resistanceRetiree." points de résistance ont étés consommés.";
		
		$perso->changePa("-",$pafinal);
		$perso->setPa();
		$perso->setComp(array('SCRS' => rand(1,3) ));
		$perso->setStat(array('AGI' => '-01', 'FOR' => '-01', 'DEX' => '+02', 'PER' => '-01', 'INT' => '+01'));
		$blesse->changePv("+",$compteurPV);
		$blesse->setPv($perso, 'Soigner (perso)');
		
		Member_He::add($perso->getId(), $blesse->getId(), 'soigner', 'Des soins sont dispensés pour ' . $compteurPV . 'PV.');
		
		//Rafraichir le HE
		//return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}
