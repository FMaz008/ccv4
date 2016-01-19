<?php
/** Gestion de l'interface de suppression d'un producteur
*
* @package Mj
*/
class Mj_Lieu_ProducteurDel
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (!isset($_POST['id']))
			return fctErrorMSG('Vous devez sélectionner un producteur.', '?mj=Lieu_Producteur',null,false);
		
		
		//Effacer l'inventaire du producteur
		$query = 'DELETE FROM ' . DB_PREFIX . 'producteur_inv
					WHERE `producteurId`=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Effacer l'association avec les lieux
		$query = 'DELETE FROM ' . DB_PREFIX . 'lieu_distributeur
					WHERE `producteurId`=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;

		
		//Effacer le producteur
		$query = 'DELETE FROM ' . DB_PREFIX . 'producteur
					WHERE `id`=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;

		
		die("<script>location.href='?mj=Lieu_Producteur';</script>");
	}
}
