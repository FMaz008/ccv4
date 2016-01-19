<?php
/**
 * Fichier de contournement du moteur de jeu.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package CronTask
 */
 
function __autoload($classname)
{
	$path = str_replace('_',DIRECTORY_SEPARATOR, $classname);
	require_once("../classes/$path.php");
	
}

require_once('../const.inc.php');



//Établir la connexion MySQL (Temps: 0.002 sec)
try
{
	$dbMan = DbManager::getInstance();
	$db = $dbMan->newConn('cron', DB_HOST, DB_USER, DB_PASS, DB_BASE);
}
catch (Exception $e)
{
    die('Impossible d\'établir la connexion: ' . $e->getMessage());
}
