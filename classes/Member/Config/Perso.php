<?php
/** Gestion des options du compte
*
* @package Member
*/
class Member_Config_Perso
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{	//BUT: Démarrer un template propre à cette page
	
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		$tpl->set('PERSO_NAME',		$perso->getNom());
		$tpl->set('REACTION',		$perso->getReaction());
		$tpl->set('ESQUIVE',		$perso->getEsquive());
		$tpl->set('CURRENT_ACTION',	$perso->getCurrentAction());
		$tpl->set('DESCRIPTION',	$perso->getDescription());
		$tpl->set('SITE_CHARSET', 	SITE_CHARSET);
		
		
		//Aller chercher le background
		$query = 'SELECT background'
				. ' FROM ' . DB_PREFIX . 'perso'
				. ' WHERE userId=:userId'
					. ' AND id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',				$perso->getId(),	PDO::PARAM_INT);
		$prep->bindValue(':userId',			$account->getId(),	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__,__LINE__);
		
		$harr = $prep->fetch();
		$tpl->set('BACKGROUND', stripslashes($harr['background']));
		
		
		
		//Génération des paramètres concernant l'image (avatar)
		$img_maxw = 150; //Px
		$img_maxh = 200; //Px
		$allowedext = array('jpg', 'jpeg', 'gif', 'png');
		$img_maxsize = 50; //Kb

		if ($perso->getAvatar()=='')
			$tpl->set("IMG_SEL",0);
		elseif(strtolower(substr($perso->getAvatar(),0,4)) == 'http')
			$tpl->set('IMG_SEL',1);
		else
			$tpl->set('IMG_SEL',2);
		
		
		$tpl->set('IMG_MAXW',$img_maxw);
		$tpl->set('IMG_MAXH',$img_maxh);
		$tpl->set('IMG_MAXSIZE',$img_maxsize);
		$tpl->set('AVATAR_URL', $perso->getAvatar());
		
		//Code concernant la publicité
		if ($account->getMemberLevel() >= 2)
		{
			$tpl->set('UPLOAD_ACCESS',true);
		}
		else
		{
			$tpl->set('PUB_TITLE','Héberger votre propre image?');
			$tpl->set('PUB_DESC','Vous désirez sauvegarder votre image directement sur le serveur du jeu?');
			$pagepub = $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/pub_mp.htm',__FILE__,__LINE__); //pub_mp_iframe.htm
			$tpl->set('PAGE_PUB',$pagepub);
		}
		
		//Générer la page
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Config/perso.htm',__FILE__,__LINE__);
		
	}
}

