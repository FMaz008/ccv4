<?php
/**
 * AJAX: Validation de la disponibilité du nom d'utilisateur
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Visitor
 * @subpackage Ajax
 */
 
class Visitor_InscriptionCheck
{
	function generatePage(&$tpl, &$session, &$account)
	{	//BUT: Démarrer un template propre à cette page
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		if (!isset($_POST['user']) || !isset($_POST['email']))
			die (rawurlencode('ERR|Rien à vérifier.'));
		
		$query = 'SELECT id'
				. ' FROM ' . DB_PREFIX . 'account'
				. ' WHERE user=:user'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':user',	$_POST['user'], PDO::PARAM_STR);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		
		if ($arr !== false)
			die('ERR|' . rawurlencode('Ce nom d\'utilisateur est déjà utilisé.'));
		
		$query = 'SELECT id'
				. ' FROM ' . DB_PREFIX . 'account'
				. ' WHERE email=:email'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':email',	$_POST['email'], PDO::PARAM_STR);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		
		if($arr !== false)
			die('ERR|' . rawurlencode('Cette adresse email est déjà utilisé.'));
		
		
		die('OK');
	}
}

