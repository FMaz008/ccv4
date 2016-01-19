<?php
/** Gestion d'un laboratoire de drogue
*
* @package Member_Action
*/
class Member_Action_Lieu_LaboDrogue2{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		$errorUrl = '?popup=1&amp;m=Action_Lieu_LaboDrogue';
		
		$coutPa = 50;
		$minQte = 10; //Quantité minimale de drogue à fournir
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
		
		
		//Vérifier si le perso possède assez de PA
		if ($perso->getPa() <= $coutPa)
			return fctErrorMSG('Vous n\'avez pas assez de PA.', $errorUrl);
		
		
		//Valider si le lieu actuel est un labo
		if(!$perso->getLieu()->isLaboDrogue())
			return fctErrorMSG('Ce lieu n\'est pas un laboratoire de drogue.', $errorUrl);
		
		
		
		
		//Valider si les drogues sélectionnés sont en possession du perso
		$i=0;
		$drogues = array();
		while( $item = $perso->getInventaire($i++))
		{
			if($item instanceof Member_ItemDrogueSubstance)
			{
				if(isset($_POST['drqte_' . $item->getInvId()]) && $_POST['drqte_' . $item->getInvId()]>0)
				{
					$drogues[] = array(
										'item'	=> $item,
										'qte'	=> $_POST['drqte_' . $item->getInvId()]		//Qte en % du comprimé que l'on souhaite produire
									);
				}
			}
		}
		
		
		//Valider les quantités (par type et totale)
		$qteTotal = 0;
		foreach($drogues as $drogue)
		{
			
			//Valider si la quantité saisie est numérique
			if(!is_numeric($drogue['qte']))
				return fctErrorMSG('Les quantités doivent être des chiffres entiers', $errorUrl);
			
			
			//Valider si le perso possède la quantité qu'il demande
			if($drogue['qte']>$drogue['item']->getQte())
				return fctErrorMSG('Vous ne pouvez pas utiliser plus que vous possèdez', $errorUrl);
			
			//Arrondir à l'entier inférieur
			$drogue['qte'] = floor($drogue['qte']);
			
			//Compiler le total
			$qteTotal+= $drogue['qte'];
		}
		
		
		
		//Valider si la quantité fourni est suffisante
		if ($qteTotal<$minQte)
			return fctErrorMSG('Une préparation demande une quantité minimale de 10.', $errorUrl);
		
		
		//Établir le % de chaque substance par rapport au total
		for($i=0; $i<count($drogues); $i++)
			$drogues[$i]['perc'] = round(100/$qteTotal * $drogues[$i]['qte']);
		
		
		
		//Déterminer la quantité de perte lors de la préparation (évaporation par exemple)
		if($qteTotal>500)
			$pertePerc = rand(5,10);
		elseif($qteTotal<500)
			$pertePerc = rand(8,15);
		elseif($qteTotal<100)
			$pertePerc = rand(10,25);
		elseif($qteTotal<50)
			$pertePerc = rand(20,40);
		elseif($qteTotal<25)
			$pertePerc = rand(30,60);
		
		
		//Mettre à jour la quantité totale à produire en fonction du taux de perte
		$qteTotal -= round($qteTotal * $pertePerc / 100);
		
		
		
		
		//Retirer la quantité d'item de l'inventaire
		foreach($drogues as $drogue)
		{
			if($drogue['item']->getQte()==$drogue['qte'])
			{
				$query = 'DELETE FROM `' . DB_PREFIX . 'item_inv`'
						. ' WHERE	`inv_persoid`=:persoId'
							. ' AND `inv_id`=:itemId'
						. ' LIMIT 1;';
				$prep = $db->prepare($query);
				$prep->bindValue(':persoId',	$perso->getId(),				PDO::PARAM_INT);
				$prep->bindValue(':itemId',		$drogue['item']->getInvId(),	PDO::PARAM_INT);
				
			}else{
				//Calculer la balance restante
				$restant = $drogue['item']->getQte() - $drogue['qte'];
				$query = 'UPDATE `' . DB_PREFIX . 'item_inv`'
						. ' SET		`inv_qte`=:qte'
						. ' WHERE	`inv_persoid`=:persoId'
							. ' AND `inv_id`=:itemId'
						. ' LIMIT 1;';
				$prep = $db->prepare($query);
				$prep->bindValue(':qte',		$restant,						PDO::PARAM_INT);
				$prep->bindValue(':persoId',	$perso->getId(),				PDO::PARAM_INT);
				$prep->bindValue(':itemId',		$drogue['item']->getInvId(),	PDO::PARAM_INT);
				
				
			}
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
		
		
		//Créer les drogues
		$taux = $perso->getChancesReussite('CHIM');
		$de = rand(1,100);
		
		
		if ($de<=$taux)
		{
			//Réussite de la production
			
			$msg = "Vous réussissez votre production avec succès.";
			$msg .= "\n" . $perso->setComp(array('CHIM' => rand(1,3) ));
			$msg .= "\n" . $perso->setStat(array('INT' => '+01', 'DEX' => '+01', 'PER' => '-02' ));
			

			
			$duree		= 0;
			$shockPa	= 0;
			$shockPv	= 0;
			$boostPa	= 0;
			$boostPv	= 0;
			$percAgi	= 0;
			$percDex	= 0;
			$percPer	= 0;
			$percFor	= 0;
			$percInt	= 0;

			$tot = count($drogues);
			if($tot>0) //Afin d'éviter les potentielles divisions par zéro plus bas.
			{
				foreach($drogues as $drogue)
				{
					$duree		+= $drogue['item']->getDuree();
					$shockPa	+= $drogue['item']->getShockPa();
					$shockPv	+= $drogue['item']->getShockPv();
					$boostPa	+= $drogue['item']->getBoostPa();
					$boostPv	+= $drogue['item']->getBoostPv();

					$percAgi	+= $drogue['item']->getPercStat("AGI");
					$percDex	+= $drogue['item']->getPercStat("DEX");
					$percPer	+= $drogue['item']->getPercStat("PER");
					$percFor	+= $drogue['item']->getPercStat("FOR");
					$percInt	+= $drogue['item']->getPercStat("INT");

				}
				
				$duree		/= $tot;
				$shockPa	/= $tot;
				$shockPv	/= $tot;
				$boostPa	/= $tot;
				$boostPv	/= $tot;
				$percAgi	/= $tot;
				$percDex	/= $tot;
				$percPer	/= $tot;
				$percFor	/= $tot;
				$percInt	/= $tot;
				
				
				//Protéger les champs
				$qteTotal = (int)$qteTotal;
				$pid = 		(int)$perso->getId();
				$duree =	(int)$duree;
				$shockPa =	(int)$shockPa;
				$shockPv =	(int)$shockPv;
				$boostPa =	(int)$boostPa;
				$boostPv =	(int)$boostPv;
				$percAgi =	(int)$percAgi;
				$percDex =	(int)$percDex;
				$percPer =	(int)$percPer;
				$percFor =	(int)$percFor;
				$percInt =	(int)$percInt;
			}
			
			//Créer les comprimés/doses de drogue
			$query = 'INSERT INTO `' . DB_PREFIX . 'item_inv`'
					. ' ('
						. ' `inv_dbid`,				`inv_qte`,				`inv_persoid`,'
						. ' `inv_duree`,			`inv_shock_pa`,			`inv_shock_pv`,		`inv_boost_pa`,		`inv_boost_pv`,'
						. ' `inv_perc_stat_agi`,	`inv_perc_stat_dex`,	`inv_perc_stat_per`,'
						. ' `inv_perc_stat_for`,	`inv_perc_stat_int`'
					. ' )'
					. ' VALUES'
					. ' ('
						. ' 2,						:qteTotal,				:persoId,'
						. ' :duree,					:shockPa,				:shockPv,			:boostPa,			:boostPv,'
						. ' :percAgi,				:percDex,				:percPer,'
						. ' :percFor,				:percInt'
					. ' );';
					
			
			$prep = $db->prepare($query);
			$prep->bindValue(':qteTotal',	$qteTotal,				PDO::PARAM_INT);
			$prep->bindValue(':persoId',	$pid,					PDO::PARAM_INT);
			$prep->bindValue(':duree',		$duree,					PDO::PARAM_INT);
			$prep->bindValue(':shockPa',	$shockPa,				PDO::PARAM_INT);
			$prep->bindValue(':shockPv',	$shockPv,				PDO::PARAM_INT);
			$prep->bindValue(':boostPa',	$boostPa,				PDO::PARAM_INT);
			$prep->bindValue(':boostPv',	$boostPv,				PDO::PARAM_INT);
			$prep->bindValue(':percAgi',	$percAgi,				PDO::PARAM_INT);
			$prep->bindValue(':percDex',	$percDex,				PDO::PARAM_INT);
			$prep->bindValue(':percPer',	$percPer,				PDO::PARAM_INT);
			$prep->bindValue(':percFor',	$percFor,				PDO::PARAM_INT);
			$prep->bindValue(':percInt',	$percInt,				PDO::PARAM_INT);
			$prep->execute();
			
			//$drogue_id = $db->lastInsertId();
			
		}
		else
		{
			//Échec de la production
			
			$msg = "Vous tentez une production de drogue... Malheureusement, le mélange échoue.";
			$msg .= "\n" . $perso->setComp(array('CHIM' => rand(1,3) ));
			$msg .= "\n" . $perso->setStat(array('INT' => '+01', 'DEX' => '+01', 'PER' => '-02' ));
		}
		
		//Copier le message dans les HE
		//echo $msg . "(de = {$de}/{$taux})";
		Member_He::add('System', $perso->getId(), 'labodrogue', $msg);

		//Mise à jour des PA
		$perso->changePa('-', $coutPa);
		$perso->setPa();
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}
