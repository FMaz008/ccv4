<?php
/** Suppression d'un livre d'une bibliothèque
*
* @package Mj
*/

class Mj_Lieu_BiblioDel
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_POST['id_livre']))
			return fctErrorMSG('Vous devez sélectionner un livre.', '?mj=Lieu_Biblio&id=' . $_POST['LIEU_ID'],null,false);
		
		
		$row = explode(';', $_POST['id_livre']);
		
		$query = 'DELETE FROM ' . DB_PREFIX . 'lieu_livre'
				. ' WHERE	lieuId=:lieuId'
					. ' AND itemDbId=:dbId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$row[0],	PDO::PARAM_INT);
		$prep->bindValue(':dbId',	$row[1],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		if(isset($_POST['LIEU_ID']))
			die("<script type=\"text/javascript\">location.href='?mj=Lieu_Biblio&id=" . (int)$_POST['LIEU_ID'] . "';</script>");
		else
			die('Supprimé.');
		
	}
}