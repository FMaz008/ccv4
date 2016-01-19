<?php
/**
 * Page de déconnexion.
 *
 * Suppression de la session et redirection vers la page d'accueil.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Visitor
 */
 
class Visitor_Logout
{
	public static function generatePage(&$tpl, &$session, &$account)
	{
		
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		$query = 'DELETE FROM ' . DB_PREFIX . 'session'
				. ' WHERE idcookie=:cookieId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':cookieId',	$session->getSSID(),		PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		
		session_destroy();
		
		//Retourner à la page d'accueil
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Visitor/logout.htm',__FILE__,__LINE__);
		
	}
	
}

