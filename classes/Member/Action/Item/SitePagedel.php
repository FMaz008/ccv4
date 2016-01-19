<?php
/** Gestion de l'action de jeter un item. Cette page est utilisée UNIQUEMENT par AJAX. des # d'erreur sont retourné, pas des message. Aucune interface graphique.
*
* @package Member_Action
*/
class Member_Action_Item_Sitepagedel
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
		
		
		if(!preg_match('/^([^\/]+)(?:[\/]([0-9]+))?(?:[?](.+))?/', $_POST['url'], $matches))
			die('03|' . rawurlencode('L\'URL du site est invalide.'));
		
		if(count($matches)<=2)
			die('04|' . rawurlencode('L\'id du site est manquant ou invalide.'));
		
		
		//Vérifier si l'URL existe 
		$site = Member_Siteweb::loadSite ($matches[1]);
		if (!$site)
			die('10|' . rawurlencode('Cette URL n\'existe pas.'));
		
		//Vérifier si l'accès est valide
		$acces = $site->checkAcces($_POST['user'], $_POST['pass']);
		
		if(!$acces)
			die('11|' . rawurlencode('Vous ne possèdez pas les autorisations nécésaires (1).'));
			
		if(!$acces->canModifier() && !$acces->isAdmin())
			die('12|' . rawurlencode('Vous ne possèdez pas les autorisations nécésaires (2).'));
		
		//Vérifier que la page appartient au site
		$i=0; $e=0;
		$found = false;
		while( $page = $site->getPage($i++))
			if($page->getId() == $matches[2])
				$found=true;
		if (!$found)
			die('12|' . rawurlencode('Cette page n\'appartiend pas à ce site.'));
		
		
		//Tout est ok, Effacer la page :(
		
		
		$query = 'DELETE FROM ' . DB_PREFIX . 'sitesweb_pages_acces'
				. ' WHERE page_id=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',			$matches[2],			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;

		
		$query = 'DELETE FROM ' . DB_PREFIX . 'sitesweb_pages
					WHERE id=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',			$matches[2],			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		
		
		
		die('OK|' . $perso->getPa()); //Tout est OK
	}
}

