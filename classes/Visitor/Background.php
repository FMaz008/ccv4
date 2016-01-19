<?php
/**
 * Affichage du background.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Visitor
 */
 
class Visitor_Background
{
	public static function generatePage(&$tpl, &$session, &$account)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		//Définir la page à afficher ( par défaut: resume )
		if(!isset($_GET['p']))
			$p="resume";
		elseif(is_numeric($_GET['p']))
			$p = (int)$_GET['p'];
		else
			return fctErrorMSG('page introuvable');
		
		
		//Validation de la page
		if($p!="resume" && !is_numeric($p))
			return fctBugReport("Format invalide", $p, __file__, __line__, __function__, __class__, __method__, true, true, true);
		
		
		if(is_numeric($p) && ($p<1 || $p>9))
			return fctBugReport("Range invalide", $p, __file__, __line__, __function__, __class__, __method__, true, true, true);
		
		
		
		
		//Fetcher le contenu adéquat
		$pageContenu = $tpl->fetch($account->getSkinRemotePhysicalPath() . '../_common/background/bg_' . $p . '.htm',__FILE__,__LINE__);
		$pageMenu = $tpl->fetch($account->getSkinRemotePhysicalPath() . '../_common/background/bg_menu.htm',__FILE__,__LINE__);
		
		
		
		//Placer le contenu de la page dans le template et appliquer les BBCodes si nécéssaires
		$tpl->set('PAGE_MENU', $pageMenu);
		$tpl->set('PAGE_CONTENU',BBCodes($pageContenu, false, false)); //Ne pas remplacer les sauts de lignes
		
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Visitor/background.htm',__FILE__,__LINE__);
		
	}
}

