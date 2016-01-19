<?php
/** Gestion de l'interface pour créer un site sur Domnet
* Cette page est incluse par Member_Action_Item_Navigateur
* @package Member_Action
*/
class Member_Action_Item_NavigateurModsite
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		
		
		preg_match('/^([^\/]+)(?:[\/]([a-z0-9]+))?(?:[?](.+))?/', $_POST['url'], $matches);
		
		if(count($matches)<3)
			return fctErrorMsg('L\'URL du site est invalide (2).');
		
		$site_url = $matches[1];
		$mod_site_url = $matches[3];
		
		//Vérifier si l'URL existe
		$site = Member_Siteweb::loadSite ($mod_site_url);
		if (!$site)
			return fctErrorMsg('Cette URL n\'existe pas.');
		
		//Vérifier si l'accès est valide
		$acces = $site->checkAcces($_POST['user'], $_POST['pass']);
		
		if(!$acces)
			return fctErrorMsg('Vous ne possèdez pas les autorisations nécésaires (1).');
			
		if(!$acces->isAdmin())
			return fctErrorMsg('Vous ne possèdez pas les autorisations nécésaires (2).');
			
		
		
		//Trouver les pages du site
		$PAGES = array();
		$i=0;
		while( $page = $site->getPage($i++))
			if($page->isPublic())
				$PAGES[] = $page;
		$tpl->set('PAGES', $PAGES);
		
		//Retourner le template complété/rempli
		$tpl->set('SITE', $site);
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/NavigateurDomnetModsite.htm',__FILE__,__LINE__);
	}
}

