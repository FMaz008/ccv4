<?php
/**
 * Affichage des règles de jeu.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Visitor
 */
 
class Visitor_Regles
{
	public static function generatePage(&$tpl, &$session, &$account)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Générer le contenu de la page
		$query = 'SELECT db_param'
				. ' FROM ' . DB_PREFIX . 'item_db'
				. ' WHERE db_id=:livreId'
				. ' LIMIT 1;'; //6 = Règles
		$prep = $db->prepare($query);
		$prep->bindValue(':livreId',	6, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$content = $prep->fetch();
		
		if ($content === false)
			return fctErrorMSG ('Texte non-trouvé :(');
		
		
		//Placer le contenu de la page dans le template
		$tpl->set('PAGE_CONTENU',BBCodes(stripslashes($content[0])));
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Visitor/regles.htm',__FILE__,__LINE__);
		
	}
}

