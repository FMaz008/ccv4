<?php
/** Gestion des casiers
*
* @package Mj
*/


/*
Sympatique requête à sauvegarder
$query = 'SELECT `inv_idcasier`, count(`inv_idcasier`)
			FROM `' . DB_PREFIX . 'item_inv`
			WHERE	`inv_idcasier` IS NOT NULL
				AND `inv_lieutech`="A.mairie"
			GROUP BY `inv_idcasier`;';
$result = $db->query($query, __FILE__, __LINE__, __FUNCTION__, __CLASS__, __METHOD__);
*/

class Mj_Lieu_Casiers
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		if(!isset($_GET['id']))
			return fctErrorMSG('Données requises manquantes.');
		
		
		
		//Trouver le nom technique de l'ID de lieu
		$query = 'SELECT nom_technique'
				. ' FROM ' . DB_PREFIX . 'lieu'
				. ' WHERE id=:lieuId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if ($arr === false)
			return fctErrorMSG('Ce lieu n\'existe pas.');
		
		$lieuTech = $arr['nom_technique'];
		$tpl->set('LIEU_TECH', $lieuTech);
		$tpl->set('LIEU_ID', (int)$_GET['id']);
		
		
		//Générer la liste des casiers de ce lieu
		$query = 'SELECT *, count(inv_idcasier) as nbr_items, SUM(db_pr) as items_pr'
				. ' FROM ' . DB_PREFIX . 'lieu_casier'
				. ' LEFT JOIN ' . DB_PREFIX . 'item_inv ON (inv_idcasier=id_casier)'
				. ' LEFT JOIN ' . DB_PREFIX . 'item_db ON (db_id=inv_dbid)'
				. ' WHERE lieuId=:lieuId'
				. ' GROUP BY id_casier;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		
		foreach($arrAll as &$arr)
		{
			if(empty($arr['items_pr']))
				$arr['items_pr'] = 0;
		}
		
		$tpl->set('CASIERS',$arrAll);
		
		
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/Casiers.htm',__FILE__,__LINE__);
	}
}
