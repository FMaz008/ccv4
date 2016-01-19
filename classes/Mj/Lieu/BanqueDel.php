<?php
/** Gestion de l'interface de suppression d'une banque
*
* @package Mj
*/
class Mj_Lieu_BanqueDel
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (!isset($_POST['id']))
			return fctErrorMSG('Vous devez sélectionner une banque.', '?mj=Lieu_Banque',null,false);
		
		
		//Trouver le # de banque
		$query = 'SELECT banque_no'
				. ' FROM ' . DB_PREFIX . 'banque'
				. ' WHERE banque_id=:banqueId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banqueId',		$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if ($arr === false)
			return fctErrorMSG('Cette banque n\'existe pas.', '?mj=Lieu_Banque',null,false);
		
		$banque_no =$arr['banque_no'];
		
		
		//Effacer les comptes
		$query = 'DELETE FROM ' . DB_PREFIX . 'banque_comptes'
					. ' WHERE `compte_banque`=:banqueNo;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banqueNo',		$banque_no,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Effacer les historiques
		$query = 'DELETE FROM ' . DB_PREFIX . 'banque_historique'
					. ' WHERE compte LIKE :banqueNo;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banqueNo',		$banque_no . '-%',	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Effacer les accès par cartes de guichet
		$query = 'DELETE FROM ' . DB_PREFIX . 'banque_cartes'
				. ' WHERE `carte_banque`=:banqueNo;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banqueNo',		$banque_no,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Effacer la banque
		$query = 'DELETE FROM ' . DB_PREFIX . 'banque'
				. ' WHERE `banque_id`=:banqueId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banqueId',		$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		die("<script>location.href='?mj=Lieu_Banque';</script>");
	}
}
