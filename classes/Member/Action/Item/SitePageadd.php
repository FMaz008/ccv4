<?php
/** Gestion de l'action de jeter un item. Cette page est utilisée UNIQUEMENT par AJAX. des # d'erreur sont retourné, pas des message. Aucune interface graphique.
*
* @package Member_Action
*/
class Member_Action_Item_Sitepageadd
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
			die('01|Votre n\'êtes pas en état d\'effectuer cette action.');
		
		
		if(!preg_match('/^[A-Za-z0-9.-_]+$/', $_POST['url'], $matches))
			die('03|L\'URL du site est invalide (' . $_POST['url'] . ').');
		
		
		
		//Vérifier si l'URL existe 
		$site = Member_Siteweb::loadSite ($_POST['url']);
		if (!$site)
			die('10|Cette URL n\'existe pas.');
		
		//Vérifier si l'accès est valide
		$acces = $site->checkAcces($_POST['user'], $_POST['pass']);
		
		if($acces===false)
			die('11|Vous ne possèdez pas les autorisations nécésaires (1).');
			
		if(!$acces->canPoste() && !$acces->isAdmin())
			die('12|Vous ne possèdez pas les autorisations nécésaires (2).');
		
		
		//Tout est ok, Créer la page !!!!! 
		
		$query = 'INSERT INTO ' . DB_PREFIX . 'sitesweb_pages'
				. ' (`site_id`, `titre`, `content`, `acces`, `showIndex`)'
				. ' VALUES'
				. ' (:site_id, :titre, :content, :acces, :showIndex);';
		$prep = $db->prepare($query);
		$prep->bindValue(':site_id',		$site->getId(),								PDO::PARAM_INT);
		$prep->bindValue(':titre',			$_POST['titre'],							PDO::PARAM_STR);
		$prep->bindValue(':content',		$_POST['content'],							PDO::PARAM_STR);
		$prep->bindValue(':acces',			$_POST['acces']=='true' ? 'pub' : 'priv',	PDO::PARAM_STR);
		$prep->bindValue(':showIndex',		($_POST['showIndex']=='true') ? '1' : '0',	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		
		$pageid = $db->lastInsertId();
		
		
		//Si la page est privé et que la personne qui crée est pas admin, lui donner droit d'accès
		if($_POST['acces'] == 'priv' && $acces['admin'] !=1)
		{
			$query = 'INSERT INTO ' . DB_PREFIX . 'sitesweb_pages_acces
					(`page_id`, `user_id`)
					VALUES
					(:page_id,:user_id);';
			$prep->bindValue(':page_id',		$pageid,			PDO::PARAM_INT);
			$prep->bindValue(':user_id',		$acces['id'],		PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
		
		

		
		
		
		die('OK|' . $perso->getPa() . '|' . $_POST['url'] . '/' . $pageid); //Tout est OK
	}
}

