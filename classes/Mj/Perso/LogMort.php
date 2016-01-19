<?php
/** Gestion des logs sur la suppression des perso
*
* @package Mj
*/
class Mj_Perso_LogMort
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		/*$query = 'SELECT l.*, p.nom'
				. ' FROM ' . DB_PREFIX . 'log_persomort as l'
				. ' LEFT JOIN ' . DB_PREFIX . 'perso as p ON (p.id = l.persoId)'
				. ' ORDER BY id;';*/
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'log_persomort'
				. ' ORDER BY timestamp DESC;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		foreach($arrAll as &$arr)
			$arr['timestamp'] = date('d/m/Y', $arr['timestamp']);
		
		
		$tpl->set("donnees",$arrAll);
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . '/html/Mj/Perso/LogMort.htm'); 
	}
}

