<?php
/** Gestion de l'interface de suppression d'une banque
*
* @package Mj
*/
class Mj_Lieu_BanqueCompteCarteDel
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		if (!isset($_POST['id']))
			return fctErrorMSG('Vous devez sélectionner une carte.', '?mj=Lieu_BanqueCompteCarte&id=' . $_GET['id'],null,false);
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Effacer les accès par cartes de guichet
		$query = 'DELETE FROM ' . DB_PREFIX . 'banque_cartes'
				. ' WHERE `carte_compte`=:compteNo;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compteNo',	$compte_no,		PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		die("<script>location.href='?mj=Lieu_BanqueCompteCarte&id=" . $_GET['id'] . "';</script>");
	}
}
