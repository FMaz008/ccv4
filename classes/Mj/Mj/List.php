<?php
/** Panel MJ
* Page de gestion des comptes MJ
* @package Mj
*/


class Mj_Mj_List
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!$mj->accessAdmin())
			return fctErrorMSG('Vous n\'avez pas accès à cette page.');
		
		
		$query = 'SELECT mj.*, a.user as user'
				. ' FROM ' . DB_PREFIX . 'mj as mj'
				. ' LEFT JOIN ' . DB_PREFIX . 'account as a ON (a.id = mj.userId)'
				. ' ORDER BY nom ASC;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$mjList = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		
		$tpl->set('MJ', $mjList);
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Mj/List.htm');
	}
}
