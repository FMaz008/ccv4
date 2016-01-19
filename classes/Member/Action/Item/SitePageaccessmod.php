<?php
/** Gestion de l'action de jeter un item. Cette page est utilisée UNIQUEMENT par AJAX. des # d'erreur sont retourné, pas des message. Aucune interface graphique.
*
* @package Member_Action
*/
class Member_Action_Item_SitePageaccessmod
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		
		
		//Valider si le joueur à accès à Internet
		if(Member_Action_Item_Navigateur::checkAccess($perso)===false)
			return fctErrorMSG('Vous n\'avez pas accès à Internet.');
		
		
		//Vérifier l'état du perso
		if(!$perso->isConscient())
			die('01|' . rawurlencode('Votre n\'êtes pas en état d\'effectuer cette action.'));
		
		
		if(!is_numeric($_POST['no']))
			die('13|Id de la page invalide');
		
		//Valider si la personne a le droit de modifier la page
		$query = 'SELECT a.modifier, a.admin, p.*, pa.id as paid, w.url'
				. ' FROM ' . DB_PREFIX . 'sitesweb_pages as p'
				. ' LEFT JOIN ' . DB_PREFIX . 'sitesweb_acces as a ON (a.site_id=p.site_id)'
				. ' LEFT JOIN ' . DB_PREFIX . 'sitesweb_pages_acces as pa ON (pa.page_id = p.id AND user_id = a.id)'
				. ' LEFT JOIN ' . DB_PREFIX . 'sitesweb as w ON (w.id = a.site_id)'
				. ' WHERE	p.id =:id' 
					. ' AND a.user=:user'
					. ' AND a.pass=:pass'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':user',		$_POST['user'],			PDO::PARAM_STR);
		$prep->bindValue(':pass',		$_POST['pass'],			PDO::PARAM_STR);
		$prep->bindValue(':id',			$_POST['no'],			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		
		if($arr===false)
			die('10|' . rawurlencode('Cette URL n\'existe pas.'));
		
		if($arr['modifier']=='0' && $arr['admin']=='0')
			die('11|' . rawurlencode('Vous ne possèdez pas les autorisations nécésaires (1).'));
		
		if($arr['admin']=='0' && $arr['acces']=='priv' && empty($arr['paid']))
			die('12|' . rawurlencode('Vous ne possèdez pas les autorisations nécésaires (2).'));
			
			
		
		
		//Tout est ok, Créer la page !!!!! 
		
		

		
		
		if($_POST['adddel']=='true')
		{
			$query = 'INSERT INTO ' . DB_PREFIX . 'sitesweb_pages_acces
						(`user_id`,`page_id`)
						VALUES
						(:user_id, :page_id);';
			$prep = $db->prepare($query);
			$prep->bindValue(':user_id',		$_POST['userid'],		PDO::PARAM_INT);
			$prep->bindValue(':page_id',		$_POST['no'],			PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
		else
		{
			$query = 'DELETE FROM ' . DB_PREFIX . 'sitesweb_pages_acces'
					. ' WHERE	`user_id` =:user_id'
						. ' AND `page_id` =:page_id;';
			$prep = $db->prepare($query);
			$prep->bindValue(':user_id',		$_POST['userid'],		PDO::PARAM_INT);
			$prep->bindValue(':page_id',		$_POST['no'],			PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
		
		
		
		
		
		
		die('OK|' . $perso->getPa() . '|' . $arr['url'] . '/' . $_POST['no']); //Tout est OK
	}
}

