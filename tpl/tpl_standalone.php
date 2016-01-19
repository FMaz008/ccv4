<?php
/**
 * Fichier servant à pouvoir utiliser la fonction getSkinLocalPhysicalPath() dans les CSS.
 *
 * @see Account::getSkinLocalPhysicalPath()
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package CSS
 */
 
if(!isset($INCLUDE_TPL))
	die('Bye.');
	
//utile dans le cas de template offline seulement
//Ce fichier sera inclu (require) dans les template, les chemins d'accès sont donc ceux des template, et non pas directement de ce fichier.
include('../../../_v5conn.inc.php');
require('../../classes/MySQLConnection.php');
require('../../classes/Session.php');
require('../../classes/Account.php');
require('../../function.php');


//Établir la connexion MySQL (Temps: 0.002 sec)
try
{
	$dbMan = DbManager::getInstance();
	$db = $dbMan->newConn('game', DB_HOST, DB_USER, DB_PASS, DB_BASE);
}
catch (Exception $e)
{
    die('Impossible d\'établir la connexion: ' . $e->getMessage());
}


//Instancier le compte
$account = new Account();

//Démarrer la session
$session = new Session($account);



