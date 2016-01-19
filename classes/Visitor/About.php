<?php
/**
 * Affichage de la page "À propos de".
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Visitor
 */

class Visitor_About
{
	public static function generatePage(&$tpl, &$session, &$account)
	{
		
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Générer la liste des MJ
		$query = 'SELECT *
					FROM ' . DB_PREFIX . 'mj;';
		$prep= $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$i=0;
		foreach($arrAll as &$arr)
		{
			$arr['present'] = ($arr['present']) ? '' : ' <span style="font-style:italic;">(absent)</span>';
			$arr['email_suffix'] = SITE_DOMAIN;
		}
		$tpl->set('MJ',$arrAll);

		//Générer la liste des donateurs
		$query = 'SELECT `user` FROM `' . DB_PREFIX . 'account` WHERE `mp` = 2;';
		$prep= $db->prepare($query);
		$prep->execute($db, __FILE__, __LINE__);
		$donatAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		$tpl->set('DONATS', $donatAll);

		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Visitor/about.htm',__FILE__,__LINE__);
		
	}
}

