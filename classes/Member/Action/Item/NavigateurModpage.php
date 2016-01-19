<?php
/** Gestion de l'interface pour modifier une page sur Domnet
* Cette page est incluse par Member_Action_Item_Navigateur
* @package Member_Action
*/
class Member_Action_Item_NavigateurModpage
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		
		//Valider si l'URL à été recu
		if(!isset($_POST['url']))
			return fctErrorMSG('url innexistante');
		
		//Séparer le site de la page (dans l'URL)
		preg_match('/^([^\/]+)(?:[\/]([a-z0-9]+))?(?:[?](.+))?/', $_POST['url'], $matches);
		
		if (count($matches)<=3)
			return fctErrorMSG('url invalide');
		
		$url_site = $matches[1];
		$url_page = $matches[2];
		$url_param= (int)$matches[3]; //Id de la page a modifier
		
		
		//Valider si la personne a le droit de modifier la page
		$query = 'SELECT a.modifier, a.admin, p.*, pa.id as paid'
				. ' FROM ' . DB_PREFIX . 'sitesweb_pages as p'
				. ' LEFT JOIN ' . DB_PREFIX . 'sitesweb_acces as a ON (a.site_id=p.site_id)'
				. ' LEFT JOIN ' . DB_PREFIX . 'sitesweb_pages_acces as pa ON (pa.page_id = p.id AND user_id = a.id)'
				. ' WHERE	p.id  = :id'
					. ' AND a.user= :user'
					. ' AND a.pass= :pass'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',		$url_param,			PDO::PARAM_INT);
		$prep->bindValue(':user',	$_POST['user'],		PDO::PARAM_STR);
		$prep->bindValue(':pass',	$_POST['pass'],		PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		if($arr === false)
			return fctErrorMSG('acces invalide (1)');
		
		if($arr['modifier']=='0' && $arr['admin']=='0')
			return fctErrorMSG('acces invalide (2)');
		
		if($arr['admin']=='0' && $arr['acces']=='priv' && empty($arr['paid']))
			return fctErrorMSG('acces invalide (3)');
		
		
		
		
		//Charger la page à modifer
		$page = new Member_SitewebPage($arr);
		$tpl->set('PAGE', $page);
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/NavigateurDomnetModpage.htm',__FILE__,__LINE__);
	}
}

