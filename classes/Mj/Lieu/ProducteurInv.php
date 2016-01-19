<?php
/** Gestion de l'interface de l'inventaire de production d'un producteur
*
* @package Mj
*/

class Mj_Lieu_ProducteurInv
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_GET['id']))
			return fctErrorMSG('Données requises manquantes.');
		
		if(isset($_POST['add']))
			self::addItem($db);
			
		if(isset($_POST['save']))
			self::save($db);
		
		
		$query = 'SELECT p.*, l.nom_technique'
				. ' FROM ' . DB_PREFIX . 'producteur as p'
				. ' LEFT JOIN ' . DB_PREFIX . 'lieu as l ON (l.id = p.lieuId)'
				. ' WHERE p.id=:id'
				. ' LIMIT 1';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
					
		if ($arr === false)
			return fctErrorMSG('Ce producteur n\'existe pas.');
		
		$tpl->set('PROD', $arr);
		
		
		//Trouver les informations sur l'inventaire
		$query = 'SELECT i.*, db.*'
				. ' FROM ' . DB_PREFIX . 'producteur_inv as i'
				. ' LEFT JOIN ' . DB_PREFIX . 'item_db as db ON (db.db_id = i.itemDbId)'
				. ' WHERE i.producteurId=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		
		$tpl->set('ITEMS', $arrAll);
		
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/ProducteurInv.htm',__FILE__,__LINE__);
	}
	
	


	
	private static function addItem(&$db)
	{
		
		if(!isset($_POST['itemId']))
			return false;
		
		$query = 'INSERT IGNORE INTO ' . DB_PREFIX . 'producteur_inv'
				. ' (producteurId, itemDbId, qte, pa_needed)'
				. ' VALUES'
				. ' (:prodId, :dbId, :qte, 0);';
		$prep = $db->prepare($query);
		foreach($_POST['itemId'] as $itemId)
		{
			$prep->bindValue(':prodId',	$_GET['id'],	PDO::PARAM_INT);
			$prep->bindValue(':dbId',	$itemId,		PDO::PARAM_INT);
			$prep->bindValue(':qte',	$_POST['item' . $itemId],	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
		}
		$prep->closeCursor();
		$prep = NULL;
	}


	
	
	private static function save(&$db)
	{
		
		
		//Trouver les informations sur l'inventaire
		$query = 'SELECT i.*'
				. ' FROM ' . DB_PREFIX . 'producteur_inv as i'
				. ' WHERE i.producteurId=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		$query = 'UPDATE ' . DB_PREFIX . 'producteur_inv'
				. ' SET qte=:qte,'
					. ' pa_needed=:pa,'
					. ' prix=:prix,'
					. ' pack=:pack'
					. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prepUpd = $db->prepare($query);

		$query = 'DELETE FROM ' . DB_PREFIX . 'producteur_inv'
					. ' WHERE id=:id'
					. ' LIMIT 1;';
		$prepDel = $db->prepare($query);
		foreach($arrAll as &$arr)
		{
			if(isset($_POST['qte_' . $arr['id']]) && isset($_POST['pa_' . $arr['id']]))
			{
				$prepUpd->bindValue(':qte',		$_POST['qte_' . $arr['id']],	PDO::PARAM_INT);
				$prepUpd->bindValue(':pa',		$_POST['pa_' . $arr['id']],		PDO::PARAM_INT);
				$prepUpd->bindValue(':prix',	$_POST['prix_' . $arr['id']],	PDO::PARAM_INT);
				$prepUpd->bindValue(':pack',	$_POST['pack_' . $arr['id']],	PDO::PARAM_INT);
				$prepUpd->bindValue(':id',		$arr['id'],						PDO::PARAM_INT);
				$prepUpd->execute($db, __FILE__, __LINE__);
			}
			
			if(isset($_POST['del_' . $arr['id']]))
			{
				$prepDel->bindValue(':id',		$arr['id'],		PDO::PARAM_INT);
				$prepDel->execute($db, __FILE__, __LINE__);
			}
		}
		$prepUpd->closeCursor();
		$prepUpd = NULL;
		$prepDel->closeCursor();
		$prepDel = NULL;
		
		
	}
	
}

