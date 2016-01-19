<?php
/** Supression d'un lieu et de ses liens
*
* @package Mj
*/
class Mj_Lieu_Del
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (!isset($_GET['id']) && !isset($_POST['id'])
			|| !isset($_GET['id']) && empty($_POST['id'])
			|| empty($_GET['id']) && !isset($_POST['id']))
			return fctErrorMSG('Vous devez sélectionner un lieu.');
		
		//Si en provenance de Tout les lieux: POST, si en provenance du panel MJ: GET
		$lieuId = (isset($_POST['id'])) ? (int)$_POST['id'] : (int)$_GET['id'];
		
		//Instancier le lieu
		try
		{
			$lieu = Member_LieuFactory::createFromId($lieuId);
		}
		catch(Exception $e)
		{
			die($e->getMessage());
		}
		
		$nom_tech = $lieu->getNomTech();
		
		$query = 'DELETE FROM ' . DB_PREFIX . 'lieu_lien'
				. ' WHERE `from`=:from OR `to`=:to;';
		$prep = $db->prepare($query);
		$prep->bindValue(':from',	$nom_tech,	PDO::PARAM_STR);
		$prep->bindValue(':to',		$nom_tech,	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//supprimer l'historique des transactions de la boutique
		$lieu->supprimerBoutiqueHistorique();
		
		$query = 'DELETE FROM ' . DB_PREFIX . 'lieu'
				. ' WHERE id=:lieuId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$lieuId,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Supprimer les gérants associés au lieu
		$query = 'DELETE FROM ' . DB_PREFIX . 'boutiques_gerants'
				. ' WHERE `boutiqueid` = :idLieu;';
		$prep = $db->prepare($query);
		$prep->bindValue(':idLieu',	$lieuId, PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		die("<script>location.href='?mj=Lieu';</script>");
	}
}
