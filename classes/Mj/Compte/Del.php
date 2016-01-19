<?php
/** Gestion de la suppression d'un compte
*
* @package Mj
*/

class Mj_Compte_Del
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!isset($_GET['id']) || empty($_GET['id']))
			return fctErrorMSG('Vous devez sélectionner un compte.');
		
		
		//suppression si le MJ est certain
		if ($_GET['action'] == 'suppr')
		{
			self::delete($_GET['id']);
			die('<script>location.href="?mj=index";</script>');
		}



		//affichage des donnees du compte :
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'account'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$donnees = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($donnees===false)
			return fctErrorMSG('Ce compte est introuvable.');
		
		$tpl->set('donnees', $donnees);


		//affichage des donnees des persos :
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'perso'
				. ' WHERE userId=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$info_pj = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		
		//envoie des infos à l'autre page (tpl)
		$tpl->set('pj',$info_pj);
			

		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Compte/Supprimer.htm');
	}





	
	public static function delete($userId)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Suppression de tous les perso du compte
		$query = 'SELECT id'
				. ' FROM ' . DB_PREFIX . 'perso'
				. ' WHERE userId=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$userId,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		
		foreach($arr as &$perso)
			Mj_Perso_Del::delete($perso['id']);
		
		
		//suppression du compte
		$query = 'DELETE FROM ' . DB_PREFIX . 'account'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$userId,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
	}
}

