<?php
/** Gestion de l'interface pour modifier une page sur Domnet
* Cette page est incluse par Member_Action_Item_Navigateur
* @package Member_Action
*/
class Member_Action_Item_NavigateurModpageacces
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		
		//Valider si l'URL est spécifiée.
		if(!isset($_POST['url']))
			return fctErrorMSG('url innexistante');
		
		//Séparer le site de la page (dans l'URL)
		preg_match('/^([^\/]+)(?:[\/]([a-z0-9]+))?(?:[?](.+))?/', $_POST['url'], $matches);
		if (count($matches)<=3)
			return fctErrorMSG('url invalide');
		
		$url_site = $matches[1];
		$url_page = $matches[2];
		$url_param= $matches[3]; //Id de la page a modifier
		
		
		//Trouver le site qui contient la page
		$query = 'SELECT s.url'
				. ' FROM ' . DB_PREFIX . 'sitesweb_pages as p'
				. ' LEFT JOIN ' . DB_PREFIX . 'sitesweb as s ON (s.id=p.site_id)'
				. ' WHERE p.id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',				$url_param,			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		if($arr === false)
			return fctErrorMSG('Page innexistante');
		
		
		//Vérifier si l'URL existe 
		$site = Member_Siteweb::loadSite ($arr['url']);
		if (!$site)
			return fctErrorMsg('Cette URL n\'existe pas.');
		
		//Vérifier si l'accès est valide
		$acces = $site->checkAcces($_POST['user'], $_POST['pass']);
		
		if(!$acces)
			return fctErrorMsg('Vous ne possèdez pas les autorisations nécésaires (1).');
			
		if(!$acces->isAdmin())
			return fctErrorMsg('Vous ne possèdez pas les autorisations nécésaires (2).');
		
		
		
		
		//Charger les accès à la page
		$query = 'SELECT id, user_id'
				. ' FROM ' . DB_PREFIX . 'sitesweb_pages_acces'
				. ' WHERE page_id=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',			$url_param,			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrPageAx = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		
		$i=0;
		$arrAcces = array();
		while( $ax = $site->getAcces($i++))
		{
			$tmp = array();
			$tmp['obj'] = $ax;
			$tmp['ax'] = false;
			
			foreach($arrPageAx as $pax)
			{
				if($pax!==false && $pax['user_id'] == $ax->getId())
				{	
					$tmp['ax'] = true;
					break;
				}
			}
			$arrAcces[] = $tmp;
		}
		
		$tpl->set('ACCES', $arrAcces);

		
		//Trouver la page
		$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'sitesweb_pages'
					. ' WHERE `id` = :id'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',			$url_param,			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		
		if($arr === false)
			return fctErrorMSG('page invalide (1)');
		
		$page = new Member_SitewebPage($arr);
		$tpl->set('PAGE', $page);
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/NavigateurDomnetModpageacces.htm',__FILE__,__LINE__);
	}
}

