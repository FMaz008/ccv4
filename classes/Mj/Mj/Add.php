<?php
/** Gestion de l'interface des déplacement
*
* @package Mj
*/
class Mj_Mj_Add
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if(!$mj->accessAdmin())
			return fctErrorMSG('Vous n\'avez pas accès à cette page.');
		
		
		if(isset($_POST['save']))
		{
			//sauvegarder l'ajout
			
				if (empty($_POST['userId']))
					return fctErrorMSG('Utilisateur manquant.');
					
				if (empty($_POST['nom']))
					return fctErrorMSG('Nom du MJ manquant.');
				
				if (isset($_POST['ax_hj']) && !is_numeric($_POST['ax_hj']))
					return fctErrorMSG('Droit d\'accès à une valeur invalide 1');
				if (isset($_POST['ax_ej']) && !is_numeric($_POST['ax_ej']))
					return fctErrorMSG('Droit d\'accès à une valeur invalide 2');
				if (isset($_POST['ax_ppa']) && !is_numeric($_POST['ax_ppa']))
					return fctErrorMSG('Droit d\'accès à une valeur invalide 3');
				if (isset($_POST['ax_admin']) && !is_numeric($_POST['ax_admin']))
					return fctErrorMSG('Droit d\'accès à une valeur invalide 4');
				if (isset($_POST['ax_dev']) && !is_numeric($_POST['ax_dev']))
					return fctErrorMSG('Droit d\'accès à une valeur invalide 5');
				
				$query = 'INSERT INTO ' . DB_PREFIX . 'mj'
						. ' (userId, nom, poste, present, email_prefix, ax_hj, ax_ej, ax_ppa, ax_admin, ax_dev)'
						. ' VALUES'
						. ' (:userId, :nom, :poste, :present, :email_prefix, :ax_hj, :ax_ej, :ax_ppa, :ax_admin, :ax_dev);';
				$prep = $db->prepare($query);
				$prep->bindValue(':userId',			$_POST['userId'],	PDO::PARAM_INT);
				$prep->bindValue(':nom',			$_POST['nom'],		PDO::PARAM_STR);
				$prep->bindValue(':poste',			$_POST['poste'],	PDO::PARAM_STR);
				$prep->bindValue(':present',		$_POST['present'],	PDO::PARAM_STR);
				$prep->bindValue(':email_prefix',	$_POST['email_prefix'],				PDO::PARAM_STR);
				$prep->bindValue(':ax_hj',			isset($_POST['ax_hj']) ? 1 : 0,		PDO::PARAM_STR);
				$prep->bindValue(':ax_ej',			isset($_POST['ax_ej']) ? 1 : 0,		PDO::PARAM_STR);
				$prep->bindValue(':ax_ppa',			isset($_POST['ax_ppa']) ? 1 : 0,	PDO::PARAM_STR);
				$prep->bindValue(':ax_admin',		isset($_POST['ax_admin']) ? 1 : 0,	PDO::PARAM_STR);
				$prep->bindValue(':ax_dev',			isset($_POST['ax_dev']) ? 1 : 0,	PDO::PARAM_STR);
				$prep->execute($db, __FILE__, __LINE__);
				$mjId = $db->lastInsertId();
				$prep->closeCursor();
				$prep = NULL;
				
				
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
					
					//Trouver le vrai email du MJ
					$query = 'SELECT a.email'
							. ' FROM ' . DB_PREFIX . 'mj as mj'
							. ' LEFT JOIN ' . DB_PREFIX . 'account as a ON (a.id=mj.userId)'
							. ' WHERE mj.id=:mjId'
							. ' LIMIT 1;';
					$prep = $db->prepare($query);
					$prep->bindValue(':mjId',	$mjId,	PDO::PARAM_INT);
					$prep->execute($db, __FILE__, __LINE__);
					$arr = $prep->fetch();
					$prep->closeCursor();
					$prep = NULL;

					$realEmail = $arr['email'];
					
					//Créer une adresse email
					$query = 'REPLACE INTO `' . DB_EMAIL_PREFIX . 'alias`'
							. ' (`id`, `alias`, `destination`, `isactive`)'
							. ' VALUES'
							. ' (NULL, :email, :destination, "1");';
					$emailCC = strtolower($_POST['nom']) . '@' . SITE_DOMAIN;
					
					$prep = $emailCon->prepare($query);
					$prep->bindValue(':email',			$emailCC,	PDO::PARAM_STR);
					$prep->bindValue(':destination',	$realEmail,	PDO::PARAM_STR);
					$prep->execute($emailCon, __FILE__, __LINE__);
					$prep->closeCursor();
					$prep = NULL;
				
					
					//Fermer la connexion
					$dbMgr->closeConn('email');
				}
				
				die("<script>location.href='?mj=Mj_List';</script>");
				
		}
		
		
		$tpl->set('ACTIONTYPETXT',"Ajouter");
		$tpl->set('SUBMITNAME','Add');
		$tpl->set('SHOWID',false);
		$tpl->set('SHOWEMAILFORWARDER', ((DB_EMAIL_HOST) ? true : false));
		$tpl->set('SITE_DOMAIN', SITE_DOMAIN);
		
		//Déprotéger les textes stockés afin qu'il soit lisibles correctement
		$arr['userId'] = '';
		$arr['nom'] = '';
		$arr['poste'] = '';
		$arr['present'] = 'M';
		$arr['email_prefix'] = '';
		$arr['ax_hj'] ='';
		$arr['ax_ej'] ='';
		$arr['ax_ppa'] ='';
		$arr['ax_admin'] ='';
		$arr['ax_dev'] = '';
		
		$tpl->set("MJ",$arr);
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Mj/Addmod.htm',__FILE__,__LINE__);
	}
}
