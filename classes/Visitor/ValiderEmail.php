<?php
/**
 * Validation du email.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Visitor
 */
 
class Visitor_ValiderEmail
{
	public static function generatePage(&$tpl, &$session, &$account)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_GET['u']) || !isset($_GET['c']))
			return fctErrorMSG('Adresse invalide.');
		
		
		$query = 'SELECT code_validation'
					. ' FROM ' . DB_PREFIX . 'account'
					. ' WHERE user=:user'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':user',	$_GET['u'], PDO::PARAM_STR);
		$prep->execute($db);
		$content = $prep->fetch();
		
		if ($content===false)
			return fctErrorMSG('Aucune correspondance pour ce nom d\'utilisateur.');
		
		
		if($content[0] != $_GET['c'])
			return fctErrorMSG('Le code de validation est invalide.');
		
		
		$query = 'UPDATE ' . DB_PREFIX . 'account'
					. ' SET code_validation = NULL'
					. ' WHERE user = :user'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':user',	$_GET['u'], PDO::PARAM_STR);
		$prep->execute($db);
		
		
		//Retourner le template complété/rempli
		if(isset($_GET['change']))
		{
			return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Visitor/validerEmailChange.htm',__FILE__,__LINE__);
		}
		else
		{
			return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Visitor/validerEmail.htm',__FILE__,__LINE__);
		}
	}
}

