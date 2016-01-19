<?php
/**
 * Affichage de la page d'inscription du compte.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Visitor
 */
 
class Visitor_Inscription
{
	function generatePage(&$tpl, &$session, &$account)
	{	//BUT: Démarrer un template propre à cette page
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		//Aller chercher les notices de pré-inscription
		$query = 'SELECT db_param'
				. ' FROM ' . DB_PREFIX . 'item_db'
				. ' WHERE db_id=:livre_id;'; //8 = NOTICES
		$prep = $db->prepare($query);
		$prep->bindValue(':livre_id',	8, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		
		if ($arr === false)
			return fctErrorMSG('Les notices de pré-inscription sont introuvables.');
		
		$tpl->set('NOTICES', BBCodes(stripslashes($arr['db_param'])));  
		
		
		
		//Aller chercher les règles HJ
		$query = 'SELECT db_param
					FROM ' . DB_PREFIX . 'item_db
					WHERE db_id=:livre_id;'; //6 = Règles HJ
		$prep = $db->prepare($query);
		$prep->bindValue(':livre_id',	6, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		
		if ($arr === false)
			return fctErrorMSG('Texte des règles HJ introuvable.');
		
		$tpl->set('REGLES', BBCodes(stripslashes($arr['db_param'])));  
		
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Visitor/inscription.htm',__FILE__,__LINE__);
		
	}
}

