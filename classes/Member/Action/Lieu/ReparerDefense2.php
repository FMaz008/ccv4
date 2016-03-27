<?php
/** Gestion de l'interface de réparation des armes
*
* @package Member_Action
*/
class Member_Action_Lieu_ReparerDefense2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		$errorUrl = '?popup=1&amp;m=Action_Lieu_ReparerDefense';
		
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
		
		if(!isset($_POST['invId']) || !is_numeric($_POST['invId']))
			return fctErrorMSG('Vous devez sélectionner un item à réparer.', $errorUrl);
		
		
		//Valider si l'arme est présente dans l'inventaire
		$i=0; $found=false;
		while( $item = $perso->getInventaire($i++))
		{
			if($item instanceof Member_ItemDefense)
			{
				if($item->getInvId() == $_POST['invId'])
				{
					$found = true;
					break;
				}
			}
		}
		
		if(!$found)
			return fctErrorMSG('L\'item ne semble pas être une arme valide en votre possession.', $errorUrl);
		
		
		$msg = '';
		
		//Calculer le % de réussite
		$chanceReussite =	(
								  $perso->getChancesReussite('ARMU') * 2
								+ $perso->getChancesReussite('FORG') * 1
								+ $perso->getChancesReussite('ARTI') * 1
								) /4;
		$chanceReussite = round(($chanceReussite + ($item->getPercDommage() + $item->getPercComplexite())/2) /2);
		
		if (DEBUG_MODE) echo "\n%dom:" .$item->getPercDommage();
		if (DEBUG_MODE) echo "\n%complex:" .$item->getPercComplexite();
		
		//Calculer le cout $/Pa de la réparation
		$coutCash 	= round(($item->getPercDommage() / 20) * ($item->getResistanceMax() - $item->getResistance()),2);
		$coutPa		= floor((100-$chanceReussite)/10 * $item->getPercDommage()/10);
		
		if($perso->getCash() < $coutCash)
			return fctErrorMSG('Vous n\'avez pas assez d\'argent pour effectuer une réparation.', $errorUrl);
		
		if($perso->getPa() <= $coutPa)
			return fctErrorMSG('Vous n\'avez pas assez de PA pour effectuer une réparation (requiert  ' . $coutPa .'PA)', $errorUrl);
		
		
		
		//Débuter la tentative de réparation
		$de = rand(1,100);
		if (DEBUG_MODE) echo "\nde/%reussite:" .$de . '/' . $chanceReussite . "\n";
		if($de < $chanceReussite)
		{
			//Réussite
			
			$msg .= "Vous arrivez à réparer votre [i]" . $item->getNom() . "[/i].";
			$perso->changePa('-', $coutPa);
			$perso->changeCash('-', $coutCash);
			
			
			//Calculer le nombre de Pts de réparés
			$artiId		= $perso->convCompCodeToId('ARTI');
			$forgId		= $perso->convCompCodeToId('FORG');
			$armuId		= $perso->convCompCodeToId('ARMU');
			$lvl = floor((
					  $perso->getCompRealLevel($armuId) * 2
					+ $perso->getCompRealLevel($forgId) * 1
					+ $perso->getCompRealLevel($artiId) * 1
					) /4);
			$msg .= "\n" . $perso->setComp(array('ARMU' => rand(2,6), 'FORG' => rand(1,3), 'ARTI' => rand(1,3) ));
			
			
			$lvl++;
			
			if(($item->getResistanceMax() - $item->getResistance()) <= $lvl)
				$newResist = $item->getResistanceMax();
			else
				$newResist = $item->getResistance() + $lvl;
			
			//Mettre à jour la résistance de l'item
			$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
						. ' SET		inv_resistance=:resistance'
						. ' WHERE	inv_id = :itemId'
							. ' AND inv_persoid = :persoId '
						. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':resistance',		$newResist,			PDO::PARAM_INT);
			$prep->bindValue(':itemId',			$item->getInvId(),	PDO::PARAM_INT);
			$prep->bindValue(':persoId',		$perso->getId(),	PDO::PARAM_INT);
			$prep->executePlus($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			$msg .= "\n" . $perso->setStat(array('INT' => '+01', 'DEX' => '+01', 'AGI' => '-02' ));
		}
		else
		{
			//Échec
			$msg .= "Vous essayez de réparer votre [i]" . $item->getNom() . "[/i], mais c'est un échec.";
			$perso->changePa('-', $coutPa);
			$perso->changeCash('-', $coutCash);
			$msg .= "\n" . $perso->setComp(array('FORG' => rand(1,3) ));
			$msg .= "\n" . $perso->setStat(array('INT' => '+01', 'DEX' => '+01', 'AGI' => '-02' ));
		}
			
		
		$perso->setPa();
		$perso->setCash();
		
		Member_He::add('System', $perso->getId(), 'reparer', $msg);
		
		
		if(!DEBUG_MODE)
			return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}

