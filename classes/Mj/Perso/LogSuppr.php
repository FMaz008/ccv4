<?php
/** Gestion des logs sur la suppression des perso
*
* @package Mj
*/
class Mj_Perso_LogSuppr
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'log_persosuppr'
				. ' ORDER BY timestamp DESC;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		
		foreach($arrAll as &$arr)
			$arr['timestamp'] = date('d/m/Y', $arr['timestamp']);
		
		$tpl->set("donnees",$arrAll);
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . '/html/Mj/Perso/LogSuppr.htm'); 
	}
}

