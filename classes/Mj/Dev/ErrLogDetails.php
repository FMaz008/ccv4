<?php

/** Visualisation d'une erreur en détail
 *
 * @package Mj_Dev
 */
 
 class Mj_Dev_ErrLogDetails
 {
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		if(!$mj->accessDev())
			return fctErrorMSG('Vous n\'avez pas accès à cette page.');
			
		if(!isset($_GET['id']))
			return fctErrorMSG('Id manquant.');
	
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		//Récupérer l'erreur dans la base de donnée
		$query = 'SELECT * FROM `' . DB_PREFIX . 'buglog`'
				. ' WHERE `id` = :id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$_GET['id'],	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__,__LINE__);
		$result = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if(count($result) == 0)
			return fctErrorMSG('L\'erreur n\'existe pas.');
		
		$tpl->set("ERROR",$result);
			
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . '/html/Mj/Dev/ErrLogDetails.htm');
	}
 }