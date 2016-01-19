<?php
/** Gestion de l'interface des déplacement
*
* @package Mj
*/
class Mj_Mj_Mod
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
				
		if(!$mj->accessAdmin())
			return fctErrorMSG('Vous n\'avez pas accès à cette page.');
		
		
		if (empty($_POST['id']) || !is_numeric($_POST['id']))
			return fctErrorMSG('Vous devez sélectionner un MJ.', '?mj=Mj_List',null,false);
		
		
		if(isset($_POST['save']))
		{
			//modification
			
				if (empty($_POST['userId']))
					return fctErrorMSG('Utilisateur manquant.');
					
				if (empty($_POST['nom']))
					return fctErrorMSG('Nom du MJ manquant.');
				
				if (isset($_POST['ax_hj']) && !is_numeric($_POST['ax_hj']))
					return fctErrorMSG('Droit d\'accès à une valeur invalide');
				if (isset($_POST['ax_ej']) && !is_numeric($_POST['ax_ej']))
					return fctErrorMSG('Droit d\'accès à une valeur invalide');
				if (isset($_POST['ax_ppa']) && !is_numeric($_POST['ax_ppa']))
					return fctErrorMSG('Droit d\'accès à une valeur invalide');
				if (isset($_POST['ax_admin']) && !is_numeric($_POST['ax_admin']))
					return fctErrorMSG('Droit d\'accès à une valeur invalide');
				if (isset($_POST['ax_dev']) && !is_numeric($_POST['ax_dev']))
					return fctErrorMSG('Droit d\'accès à une valeur invalide');
				
				
				//Trouver le vrai email email du MJ et le préfixe actuel de son email
				$query = 'SELECT mj.email_prefix, a.email'
						. ' FROM ' . DB_PREFIX . 'mj as mj'
						. ' LEFT JOIN ' . DB_PREFIX . 'account as a ON (a.id=mj.userId)'
						. ' WHERE mj.id=:mjId'
						. ' LIMIT 1;';
				$prep = $db->prepare($query);
				$prep->bindValue(':mjId',	$_POST['id'],	PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
				$arr = $prep->fetch();
				$prep->closeCursor();
				$prep = NULL;

				$forwEmail = $arr['email_prefix'];
				$realEmail = $arr['email'];
				
				
				//Mettre à jours les modifications
				$query = 'UPDATE ' . DB_PREFIX . 'mj'
						. ' SET'
							. ' userId=		:userId,'
							. ' nom=		:nom,'
							. ' poste=		:poste,'
							. ' present=	:present,'
							. ' email_prefix=:email_prefix,'
							. ' ax_hj=		:ax_hj,'
							. ' ax_ej=		:ax_ej,'
							. ' ax_ppa=		:ax_ppa,'
							. ' ax_admin=	:ax_admin,'
							. ' ax_dev = 	:ax_dev'
						. ' WHERE id=:mjId'
						. ' LIMIT 1;';
				$prep = $db->prepare($query);
				$prep->bindValue(':mjId',	$_POST['id'],		PDO::PARAM_INT);
				$prep->bindValue(':userId',	$_POST['userId'],	PDO::PARAM_INT);
				$prep->bindValue(':nom',	$_POST['nom'],		PDO::PARAM_STR);
				$prep->bindValue(':poste',	$_POST['poste'],	PDO::PARAM_STR);
				$prep->bindValue(':present',$_POST['present'],	PDO::PARAM_INT);
				$prep->bindValue(':email_prefix',	$_POST['email_prefix'],				PDO::PARAM_STR);
				$prep->bindValue(':ax_hj',			isset($_POST['ax_hj']) ? 1 : 0,		PDO::PARAM_INT);
				$prep->bindValue(':ax_ej',			isset($_POST['ax_ej']) ? 1 : 0,		PDO::PARAM_INT);
				$prep->bindValue(':ax_ppa',			isset($_POST['ax_ppa']) ? 1 : 0,	PDO::PARAM_INT);
				$prep->bindValue(':ax_admin',		isset($_POST['ax_admin']) ? 1 : 0,	PDO::PARAM_INT);
				$prep->bindValue(':ax_dev',			isset($_POST['ax_dev']) ? 1 : 0,	PDO::PARAM_INT);
				$prep->execute($db, __FILE__, __LINE__);
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
					
					
					
					//Mettre à jour l'adresse email
					$query = 'DELETE FROM `' . DB_EMAIL_PREFIX . 'alias`'
							. ' WHERE `alias` = :alias;';
					$prep = $emailCon->prepare($query);
					$prep->bindValue(':alias',	$emailCCOld,	PDO::PARAM_STR);
					$prep->execute($emailCon, __FILE__, __LINE__);
					$prep->closeCursor();
					$prep = NULL;
					
					
					$query = 'REPLACE INTO `' . DB_EMAIL_PREFIX . 'alias`'
							. ' (`id`, `alias`, `destination`, `isactive`)'
							. ' VALUES'
							. ' (NULL, :alias, :realEmail, "1");';
					$emailCCOld = strtolower($forwEmail) . '@' . SITE_DOMAIN;
					$emailCCNew = strtolower($_POST['email_prefix']) . '@' . SITE_DOMAIN;

					$prep = $emailCon->prepare($query);
					$prep->bindValue(':alias',	$emailCCNew,	PDO::PARAM_STR);
					$prep->bindValue(':realEmail',	$realEmail,	PDO::PARAM_STR);
					$prep->execute($emailCon, __FILE__, __LINE__);
					$prep->closeCursor();
					$prep = NULL;
				
					//Fermer la connexion
					$dbMgr->closeConn('email');
				}
				
				
				die("<script>location.href='?mj=Mj_List';</script>");
				
		}
		
		
		$tpl->set('ACTIONTYPETXT',"Modifier");
		$tpl->set('SUBMITNAME','Mod');
		$tpl->set('SHOWID',true);
		$tpl->set('SHOWEMAILFORWARDER', ((DB_EMAIL_HOST) ? true : false));
		$tpl->set('SITE_DOMAIN', SITE_DOMAIN);
		
		$query = 'SELECT mj.*, a.user as user'
				. ' FROM ' . DB_PREFIX . 'mj as mj'
				. ' LEFT JOIN ' . DB_PREFIX . 'account as a ON (a.id = mj.userId)'
				. ' WHERE mj.id=:mjId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':mjId', $_POST['id'], 	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr === false)
			return fctErrorMSG('MJ #' . $_POST['id'] . ' innexistant.');

		
		//Déprotéger les textes stockés afin qu'il soit lisibles correctement
		$arr['nom'] = stripslashes($arr['nom']);
		$arr['poste'] = stripslashes($arr['poste']);
		$arr['email_prefix'] = stripslashes($arr['email_prefix']);

		$tpl->set('MJ',$arr);
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Mj/Addmod.htm',__FILE__,__LINE__);
	}
}

