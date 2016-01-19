<?php
/**
 * Page d'accueil du site.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Visitor
 */
 
class Visitor_Main
{
	public static function generatePage(&$tpl, &$session, &$account)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante

		
		//Afficher un message critique sur la page principale.
		if(defined('WARNING_MESSAGE') && WARNING_MESSAGE !== NULL)
		{
			$tpl->set('WARNING_MESSAGE', WARNING_MESSAGE);
		}

		
		//Générer le contenu de la page
		$query = 'SELECT db_param'
				. ' FROM ' . DB_PREFIX . 'item_db'
				. ' WHERE db_id=:livreId'
				. ' LIMIT 1;'; //4 = Texte page d'intro
		$prep = $db->prepare($query);
		$prep->bindValue(':livreId',	4, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$content = $prep->fetch();
		
		if ($content === false)
			return fctErrorMSG ('Texte non-trouvé :(');
		
		
		//Placer le contenu de la page dans le template
		$tpl->set('PAGE_CONTENU',BBCodes(stripslashes($content[0])));
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Visitor/main.htm',__FILE__,__LINE__);
		
	}
}

