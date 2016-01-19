<?php
/** AJAX: Modification d'un site
*
* @package Member_Action
* @subpackage Ajax
*/
class Member_Action_Item_Sitemod
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
		
		
		if (!preg_match('/^[A-Za-z0-9.-_]*$/', $_POST['url'], $matches))
			return fctErrorMsg('L\'URL du site est invalide.');
		
		
		//Vérifier si l'URL existe 
		$site = Member_Siteweb::loadSite ($_POST['url']);
		if (!$site)
			die('10|' . rawurlencode('Cette URL n\'existe pas.'));
		
		//Vérifier si l'accès est valide
		$acces = $site->checkAcces($_POST['user'], $_POST['pass']);
		
		if($acces===false)
			die('11|' . rawurlencode('Vous ne possèdez pas les autorisations nécésaires (1).'));
			
		if(!$acces->isAdmin())
			die('12|' . rawurlencode('Vous ne possèdez pas les autorisations nécésaires (2).'));
		
		
		
			
			
		//Tout est ok, modifier le site !!!!! 
		
		
		//Valider si la page d'accueil demandée appartiend au site
		$query = 'SELECT id'
				. ' FROM ' . DB_PREFIX . 'sitesweb_pages'
				. ' WHERE	site_id = :site_id'
					. ' AND id = :id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':site_id',	$site->getId(),		PDO::PARAM_INT);
		$prep->bindValue(':id',			$_POST['accueil'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
			
		if ($arr === false)
			$_POST['accueil'] = 0;
		
		$query = 'UPDATE ' . DB_PREFIX . 'sitesweb'
				. ' SET	`url`	=:urlNew,'
					. ' `titre`	=:titre,'
					. ' `acces` =:acces,'
					. ' `first_page`=:firstPage'
				. ' WHERE url=:urlOld;';
		$prep = $db->prepare($query);
		$prep->bindValue(':urlNew',		$_POST['new_url'],		PDO::PARAM_STR);
		$prep->bindValue(':titre',		$_POST['titre'],		PDO::PARAM_STR);
		$prep->bindValue(':acces',		$_POST['acces'],		PDO::PARAM_STR);
		$prep->bindValue(':firstPage',	$_POST['accueil'],		PDO::PARAM_INT);
		$prep->bindValue(':urlOld',		$_POST['url'],			PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		die('OK|' . $perso->getPa() . '|' . $_POST['new_url']); //Tout est OK
	}
}
