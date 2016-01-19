<?php
/** Gestion de l'interface de suppression d'une banque
*
* @package Mj
*/
class Mj_Lieu_BanqueCompteDel
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		

		if (!isset($_POST['id']))
			return fctErrorMSG('Vous devez sélectionner un compte.', '?mj=Lieu_BanqueCompte');
		
		
		
		//Trouver le # de compte
		$query = 'SELECT compte_compte'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE compte_id=:compteId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compteId',	$_POST['id'],		PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		$compte_no = $arr['compte_compte'];
		
		
		//Effacer les comptes
		$query = 'DELETE FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE `compte_compte`=:compteNo;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compteNo',	$compte_no,		PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Effacer les historiques
		$query = 'DELETE FROM ' . DB_PREFIX . 'banque_historique'
				. ' WHERE compte LIKE :compteNo;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compteNo',	'%-' . $compte_no,		PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Effacer les accès par cartes de guichet
		$query = 'DELETE FROM ' . DB_PREFIX . 'banque_cartes'
				. ' WHERE `carte_compte`=:compteNo;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compteNo',	$compte_no,		PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Effacer la banque
		$query = 'DELETE FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE `compte_id`=:compteId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compteId',	$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		
		die("<script>location.href='?mj=Lieu_BanqueCompte&id=" . (int)$_GET['bankId'] . "';</script>");
	}
}
