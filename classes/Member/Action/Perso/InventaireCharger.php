<?php
/** Gestion de l'action d'équiper un item. Cette page est utilisée UNIQUEMENT par AJAX. des # d'erreur sont retourné, pas des message. Aucune interface graphique.
*
* @package Member_Action
*/
class Member_Action_Perso_InventaireCharger
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$actionPa = 10;
		
		if (!isset($_POST['id']) || (isset($_POST['id']) && !is_numeric($_POST['id'])))
			die('00|' . rawurlencode('Vous devez sélectionner un item pour effectuer cette action.'));
			
		//Vérifier l'état du perso
		if(!$perso->isConscient())
			die($_POST['id'] . '|' . rawurlencode('Votre n\'êtes pas en état d\'effectuer cette action.'));
		
		if($perso->getPa() <= $actionPa)
			die($_POST['id'] . '|' . rawurlencode('Vous n\'avez pas assez de PA pour effectuer cette action.'));
		
		if($perso->getMenotte()!==false)
			die($_POST['id'] . '|' . rawurlencode('Vous êtes menotté et cette action est trop complexe.'));
		
		
		//Trouver en inventaire l'item que l'on souhaite charger
		$i=0; $item = null;
		while( $tmp = $perso->getInventaire($i++))
			if($tmp->getInvId() == $_POST['id'])
				$item = $tmp;		
		if(empty($item))
			die($_POST['id'] . '|' . rawurlencode('Cet item ne vous appartient pas. (cheat)'));
			
		if($item->getMunition()>=$item->getMunitionMax())
			die($_POST['id'] . '|' . rawurlencode('Votre arme est déjà remplie à pleine capacité.'));
			
		
		if (!isset($_POST['munid'])) // Proposer des munitions pour les charger dans l'arme
		{
			//Trouver les munitions compatibles possédés en inventaire
			$query = 'SELECT mun.inv_qte, mun.inv_id, db.db_nom'
					. ' FROM ' . DB_PREFIX . 'item_inv as mun,'
						 . ' ' . DB_PREFIX . 'item_db_armemunition as tm'
					. ' LEFT JOIN ' . DB_PREFIX . 'item_db as db ON (db.db_id = tm.db_armeid)'
					. ' WHERE mun.inv_persoid=:persoId'
						. ' AND tm.db_munitionid = mun.inv_dbid'
						. ' AND tm.db_armeid =:itemDbId;';
			$prep = $db->prepare($query);
			$prep->bindValue(':itemDbId',	$item->getDbId(),	PDO::PARAM_INT);
			$prep->bindValue(':persoId',	$perso->getId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arrMun = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;

			
			if(count($arrMun) == 0)
				die($_POST['id'] . '|' . rawurlencode('Vous ne possédez aucune munition compatible avec cette arme.'));
				
			$msg = '';
			foreach($arrMun as &$arr)
				$msg .= "<a href=\"#\" onclick=\"submitMunForm('?m=Action_Perso_InventaireCharger'," . $_POST['id'] . "," . $arr['inv_id'] . ");\">" . rawurlencode(stripslashes($arr['db_nom'])) . "</a><br />";
			
			die($_POST['id'] . '|' . $msg);
			
		}else{ // Charger les munitions dans l'arme
			//Trouver en inventaire les munitions que l'on souhaite charger
			$i=0;
			$mun = null;
			while( $tmp = $perso->getInventaire($i++))
				if($tmp->getInvId() == $_POST['munid'])
					$mun = $tmp;
			
			if(empty($mun))
				die($_POST['id'] . '|' . rawurlencode('Cette munition ne vous appartient pas. (cheat)'));
			
			$munQte = $mun->getQte();
			$munReq = $item->getMunitionMax() - $item->getMunition();
			
			if ($munReq > $munQte) //Plus de munition nécéssaire que disponible, tout charger
			{
				$mun->setQte(0);
				$item->setMunition($item->getMunition()+$munQte);
			}
			else
			{
				$mun->setQte($munQte-$munReq);
				$item->setMunition($item->getMunitionMax());
				$munQte = $munReq; //On a chargé seulement le nécéssaire
			}
			
			$perso->refreshInventaire(); //Recalculer l'inventaire (les PR)
			$perso->changePa('-', $actionPa);
			$perso->setPa();
			
			Member_He::add('System', $perso->getId(), 'charger', 'Vous chargez votre ' . $munQte . 'x[i]' . $mun->getNom() . '[/i] dans votre [i]' . $item->getNom() . '[/i].');
			
			die($_POST['id'] . '|OK|' . $perso->getPa() . '|' . $perso->getPr() . '|' . $item->getMunition() . '/' . $item->getMunitionMax() . '|' . $mun->getInvId() . '|' . $mun->getQte()); //Tout est OK
		}
	
	}
}
