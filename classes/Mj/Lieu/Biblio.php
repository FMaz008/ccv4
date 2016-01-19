<?php
/** Gestion des casiers
*
* @package Mj
*/

class Mj_Lieu_Biblio
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
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'lieu_livre'
				. ' LEFT JOIN ' . DB_PREFIX . 'item_db ON (db_id=itemDbId)'
				. ' WHERE lieuId=:lieuId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$LIVRES = array();
		foreach($arrAll as &$arr)
		{
			$arr['inv_extradesc'] = '';
			$arr['inv_equip'] = NULL;
			$arr['inv_cacheno'] = NULL;
			$arr['inv_cachetaux'] = NULL;
			$arr['inv_resistance'] = NULL;
			$LIVRES[] = new Member_ItemLivre($arr);
		}
		
		$tpl->set('LIVRES',$LIVRES);
		
		
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/Biblio.htm',__FILE__,__LINE__);
	}
}
