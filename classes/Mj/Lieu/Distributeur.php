<?php
/** Gestion de l'interface de gestion des distributeurs (qui distribue les produits des producteurs)
*
* @package Mj
*/

class Mj_Lieu_Distributeur
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		if(isset($_POST['del']))
			self::delete();
			
		if(isset($_POST['add']))
			self::add();
			
		
		
		$query = 'SELECT p.id, dl.nom_technique as dnom_technique, pl.nom_technique as pnom_technique'
				. ' FROM ' . DB_PREFIX . 'lieu_distributeur as d'
				. ' LEFT JOIN ' . DB_PREFIX . 'lieu as dl ON (dl.id = d.lieuId)'
				. ' LEFT JOIN ' . DB_PREFIX . 'producteur as p ON (p.id = d.producteurId)'
				. ' LEFT JOIN ' . DB_PREFIX . 'lieu as pl ON (pl.id = p.lieuId)'
				. ' WHERE d.lieuId = :lieuId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId', $_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$PROD = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		if (count($PROD)>0)
			$tpl->set('PRODUCTEURS', $PROD);
		
		
		
		//Liste complète des producteurs
		$query = 'SELECT p.id, l.nom_technique'
				. ' FROM ' . DB_PREFIX . 'producteur as p'
				. ' LEFT JOIN ' . DB_PREFIX . 'lieu as l'
					. ' ON (l.id = p.lieuId);';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$_POST['LIEU_ID'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$PROD = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$tpl->set('ADD_PRODUCTEURS', $PROD);
		$tpl->set('LIEU_ID', $_GET['id']);
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/Distributeur.htm',__FILE__,__LINE__);
	}
	
	
	private static function add()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_POST['id']))
			return false;
		
		
		$query = 'INSERT IGNORE INTO ' . DB_PREFIX . 'lieu_distributeur'
				. ' (lieuId, producteurId)'
				. ' VALUES'
				. ' (:lieuId, :prodId);';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$_GET['id'],	PDO::PARAM_INT);
		$prep->bindValue(':prodId',	$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
	}
	
	private static function delete()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_POST['id']))
			return false;
		
		$query = 'DELETE FROM ' . DB_PREFIX . 'lieu_distributeur'
				. ' WHERE id=:distId'
				. ' LIMIT 1';
		$prep = $db->prepare($query);
		$prep->bindValue(':distId',	$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
	
}

