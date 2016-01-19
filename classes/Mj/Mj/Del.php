<?php
/** Gestion de la suppression d'un compte MJ
*
* @package Mj
*/
class Mj_Mj_Del{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!$mj->accessAdmin())
			return fctErrorMSG('Vous n\'avez pas accès à cette page.');
		
		
		if (!isset($_POST['id']))
			return fctErrorMSG('Vous devez sélectionner un MJ.', '?mj=Mj_List',null,false);
		
		//Établir la connection MySQL à la base des redirecteurs PostFix
		if(DB_EMAIL_HOST && DB_EMAIL_HOST!==NULL)
		{
			//Établir la connection MySQL à la base des redirecteurs PostFix
			try
			{
				$emailCon = $dbMgr->newConn('email', DB_EMAIL_HOST, DB_EMAIL_USER, DB_EMAIL_PASS, DB_EMAIL_BASE);
			}
			catch (Exception $e)
			{
				die('Impossible d\'établir la connexion: ' . $e->getMessage());
			}
			
			//Trouver l'ancien préfixe email du MJ
			$query = 'SELECT email_prefix'
					. ' FROM ' . DB_PREFIX . 'mj'
					. ' WHERE id=:mjId'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':mjId',	$_POST['id'],	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arr = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;

			$forwEmail = $arr['email_prefix'];
			
			//Supprimer le redirecteur EMail
			$query = 'DELETE FROM `' . DB_EMAIL_PREFIX . 'alias`
						WHERE `alias`=:email;';
			$emailCC = strtolower($forwEmail) . '@' . SITE_DOMAIN;
			
			$prep = $emailCon->prepare($query);
			$prep->bindValue(':email',	$emailCC,	PDO::PARAM_STR);
			$prep->execute($emailCon, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			//Fermer la connexion
			$dbMgr->closeConn('email');
		}
		
		//Supprimer le compte MJ
		$query = 'DELETE FROM ' . DB_PREFIX . 'mj'
				. ' WHERE id=:mjId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':mjId',	$_POST['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;

		die("<script>location.href='?mj=Mj_List';</script>");
	}
}
