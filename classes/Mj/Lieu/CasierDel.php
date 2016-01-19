<?php
/** Suppression d'un casier
*
* @package Mj
*/

class Mj_Lieu_CasierDel
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$query = 'DELETE FROM ' . DB_PREFIX . 'lieu_casier'
				. ' WHERE id_casier=:idCasier'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':idCasier',	$_POST['id_casier'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		if(isset($_POST['LIEU_ID']))
			die("<script type=\"text/javascript\">location.href='?mj=Lieu_Casiers&id=" . $_POST['LIEU_ID'] . "';</script>");
		else
			die("Supprimé.");
		
	}
}