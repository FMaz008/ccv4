<?php
/** Gestion de l'interface de suppression d'un casino
*
* @package Mj
*/
class Mj_Lieu_CasinoDel
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (!isset($_POST['id']))
			return fctErrorMSG('Vous devez sélectionner un casino.', '?mj=Lieu_Casino',null,false);
		
		
		//Effacer la banque
		$query = 'DELETE FROM ' . DB_PREFIX . 'casino'
				. ' WHERE `casino_id`=:casinoId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':casinoId',	$_POST['id'],			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		die("<script>location.href='?mj=Lieu_Casino';</script>");
	}
}
