<?php
/**
 * AJAX: Valide si un nom de personnage est disponible.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
* @package Member
* @subpackage Inscription
 */
 
class Member_CreerPerso2Check
{
	function generatePage(&$tpl, &$session, &$account)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Vérifier si le nom de personnage est déjà utilisé par un autre.
		if (!isset($_POST['nom']))
			die (rawurlencode('ERR|Rien à vérifier.'));
		
		$query = 'SELECT id'
				. ' FROM ' . DB_PREFIX . 'perso'
				. ' WHERE nom=:nom'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':nom',	$_POST['nom'],	PDO::PARAM_STR);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		
		if($arr!==false)
			die('ERR|' . 'Ce nom est déjà utilisé.');
		
		die('OK');
	}
}

