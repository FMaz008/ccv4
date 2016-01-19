<?php
/**
 * AJAX: Confirmer l'existence d'un utilisateur.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Mp
 * @subpackage Ajax
 */
class Member_Mp_ConfirmUser
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$actionPa = 3;
		
		if (!isset($_POST['user']))
			die('00|Aucun utilisateur spécifié.');
		
		
		//Rechercher dans la base de donnée pour trouver l'utilisateur
		$query = 'SELECT id, user, mp, mp_expiration'
				. ' FROM . ' . DB_PREFIX . 'account'
				. ' WHERE user=:user'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':user',	$_POST['user'],	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr === false)
			die('00|L\'utilisateur mentionné n\'existe pas.');
		
		
		die('OK|' . $arr['id'] . '|' . $arr['user'] . '|' . $arr['mp']);  //Tout est OK
	}
}

