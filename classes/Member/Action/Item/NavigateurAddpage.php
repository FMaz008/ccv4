<?php
/** Gestion de l'interface pour modifier une page sur Domnet
*
* @package Member_Action
*/
class Member_Action_Item_NavigateurAddpage
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		
		
		
		//
		if(!isset($_POST['url']))
			return fctErrorMSG('url innexistante');
		
		
		//Séparer le site de la page (dans l'URL)
		preg_match('/^([^\/]+)(?:[\/]([a-z0-9]+))?(?:[?](.+))?/', $_POST['url'], $matches);
		if (count($matches)<=3)
			return fctErrorMSG('url invalide');
		
		$url_site = $matches[1];
		$url_page = $matches[2];
		$url_param= $matches[3]; //url du site en parametre a dom net
		
		
		//Vérifier si l'URL existe 
		$site = Member_Siteweb::loadSite($url_param);
		if (!$site)
			return fctErrorMSG('Cette URL n\'existe pas.');
		
		$tpl->set('SITE', $site);
		
		//Vérifier si l'accès est valide
		$acces = $site->checkAcces($_POST['user'], $_POST['pass']);
		
		if(!$acces)
			return fctErrorMSG('Vous ne possèdez pas les autorisations nécésaires (1).');
			
		if(!$acces->canPoste() && !$acces->isAdmin())
			return fctErrorMSG('Vous ne possèdez pas les autorisations nécésaires (2).');
		
		
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/NavigateurDomnetAddpage.htm',__FILE__,__LINE__);
	}
}

