<?php
/** Gestion de l'interface de l'action Parler: Afficher l'interface pour parler.
* Cette page est inclu toutes les sous-pages Navigateur...
* @package Member_Action
*/
class Member_Action_Item_Navigateur
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		//Vérifier l'état du perso
		if(!$perso->isConscient())
			return fctErrorMsg('Votre n\'êtes pas en état d\'effectuer cette action.');
		
			
		//Valider si le joueur à accès à Internet
		if(self::checkAccess($perso)===false)
			return fctErrorMSG('Vous n\'avez pas accès à Internet.');
		
				
			
			
			
			
			
		
		
		
		
		//## AFFICHER L'ENTÊTE DU NAVIGATEUR
		if (!isset($_POST['url']) || empty($_POST['url']))
			$_POST['url'] = "dom.net";
		
		$tpl->set('URL', $_POST['url']);
		$tpl->set('USER', ((isset($_POST['user'])) ? $_POST['user'] : ''));
		$tpl->set('PASS', ((isset($_POST['pass'])) ? $_POST['pass'] : ''));
		
		
		//## AFFICHER LE CONTENU DE LA PAGE
		
		
		
		//Séparer le site de la page (dans l'URL)
		preg_match('/^([^\/]+)(?:[\/]([a-z0-9]+))?(?:[?](.+))?/', $_POST['url'], $matches);
		
		//$tmp = explode('/',$_POST['url']);
		$url_site = null;
		$url_page = null;
		$url_param= null;
		
		if (count($matches)>1)	$url_site = $matches[1];
		if (count($matches)>2)	$url_page = $matches[2];
		if (count($matches)>3)	$url_param= $matches[3];
		
		
		//Charger le site
		$site = Member_Siteweb::loadSite($url_site);
		$tpl->set('url_param', !empty($url_param) ? $url_param : '');
		
		//Charger l'entête (le menu de navigation)
		$header = $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/NavigateurHeader.htm',__FILE__,__LINE__);
		$tpl->set('HEADER', $header);
		
		
		
		
		

		//Vérifier les droits d'accès au site
		$acces=false;
		if ($site && isset($_POST['user']) && isset($_POST['pass']))
			$acces = $site->checkAcces($_POST['user'], $_POST['pass']);
		
		
		
		if($site===false) //Vérifier si le site est existant
		{
			$page_source = $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/NavigateurNotFound.htm',__FILE__,__LINE__);
		
		
		}
		elseif($site && !$site->isPublic() && !$acces) //Si privé, si on a accès
		{
			$page_source = $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/NavigateurAccessDenied.htm',__FILE__,__LINE__);
		
		
		}
		elseif(is_numeric($url_page) || empty($url_page))//Site perso ?
		{
			//Il s'agit d'une page régulière

			$page_source = self::loadPageSource(
										$account,
										$site,
										$url_page,
										((isset($_POST['user'])) ? $_POST['user'] : ''),
										((isset($_POST['pass'])) ? $_POST['pass'] : ''),
										$acces
									);
			
		
		}
		else //Site system ?
		{
			//Il s'agit d'une page système
			switch($url_page)
			{
				case 'addsite':
					$page_source = Member_Action_Item_NavigateurAddsite::generatePage($tpl, $session, $account, $perso);
					break;
				case 'addpage':
					$page_source = Member_Action_Item_NavigateurAddpage::generatePage($tpl, $session, $account, $perso);
					break;
				case 'modpage':
					$page_source = Member_Action_Item_NavigateurModpage::generatePage($tpl, $session, $account, $perso);
					break;
				case 'modsite':
					$page_source = Member_Action_Item_NavigateurModsite::generatePage($tpl, $session, $account, $perso);
					break;
				case 'modacces':
					$page_source = Member_Action_Item_NavigateurModacces::generatePage($tpl, $session, $account, $perso);
					break;
				case 'modpageacces':
					$page_source = Member_Action_Item_NavigateurModpageacces::generatePage($tpl, $session, $account, $perso);
					break;
				default:
					$page_source = $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/navigateurNotFound.htm',__FILE__,__LINE__);
			}
		}
		
		//Vérifier si la source -- du site retourné -- comprend un titre ou seulement la source
		if(count($page_source)==2)
		{
			$tpl->set('TITRE', $page_source[0]);
			$tpl->set('PAGE', $page_source[1]);
		}
		else
		{
			$tpl->set('PAGE', $page_source);
		}
		
		//Charger l'index
		if($site && ($site->isPublic() || $acces))
		{
			$tpl->set('site', $site);
			
			$i=0; $e=0; $arrPages = array();
			while( $page = $site->getPage($i++))
				if($page->getShowIndex())
					$arrPages[$e++] = $page;
			
			$tpl->set('arrPages', $arrPages);
			
			
			//Valider les accès
			if($acces && ($acces->canPoste() || $acces->isAdmin()))
				$tpl->set('canPost', true);
			
			if($acces && $acces->isAdmin())
				$tpl->set('admin', true);
			
			$page_source = $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/NavigateurIndex.htm',__FILE__,__LINE__);
			$tpl->set('INDEX', $page_source);
		}
		
		
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/Navigateur.htm',__FILE__,__LINE__);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	private static function loadPageSource(&$account, &$site, $url_page, $user, $pass, $acces)
	{
		
		$tpl = new Template($account);
		$tpl->set('site', $site);
		$tpl->set('SITE_ACCES', $acces);
		
		//Si aucune URL de page spécifiée, charger la page d'accueil
		if(empty($url_page))
		{
			$first_page = $site->getFirstPage(); //Charger la 'first page'
			if ($first_page)
				return $first_page->getContentHTML();
			else
				return ''; //Aucune page demandé + aucune page d'accueil
		}
		
		
		//Si une URL de page est spécifiée, charger la page (Valide si la page appartiend au site)
		$i=0; $found=false;
		while( $page = $site->getPage($i++))
		{
			if($page->getId() == $url_page)
			{
				$found=true;
				break;
			}
		}
		if (!$found)
			return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/NavigateurNotFound.htm',__FILE__,__LINE__);
		
		
		
		//Vérifier les accès
		$site_acces = $site->checkAcces($user, $pass);
		$tpl->set('PAGE_ACCES', $site);
		
		if(!$page->isPublic())
			if($site_acces===false || !$page->checkAcces($user, $pass))
				if($site_acces===false || !$site_acces->isAdmin())
					return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/NavigateurAccessDenied.htm',__FILE__,__LINE__);
		
		$page_source = $page->getContentHTML();
		$tpl->set('page', $page);
		if ($site_acces && ($site_acces->isAdmin() || $site_acces->canModifier()))
		{
			if($site_acces->isAdmin() && !$page->isPublic())
				$tpl->set('SHOW_GESTION_ACCES', true);
			$page_source .= $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/NavigateurPageControl.htm',__FILE__,__LINE__);;
		}
		return array($page->getTitre(), $page_source);
	}
	
	
	
	/** Valide si un perso à accès à Internet
	*
	* @return bool TRUE si accès
	*/
	public static function checkAccess(&$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Vérifier si le personnage est dans un lieu qui possède un ordinateur fixe
		$query = 'SELECT id'
				. ' FROM ' . DB_PREFIX . 'lieu_menu'
				. ' WHERE lieutech=:lieuTech'
					. ' AND url="OrdinateurFixe"'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuTech',	$perso->getLieu()->getNomTech(), PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		
		if($arr !== false)
			return true;
			
		//Vérifier si un item a accès à internet
		$i=0;
		while( $item = $perso->getInventaire($i++))
			if($item instanceof Member_ItemOrdinateur && $item->isInternetCapable())
				return true;
		
		return false;
			
	}
}
