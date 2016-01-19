<?php
/**
 * Affichage de la page de connexion au jeu
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Visitor
 */
 
class Visitor_Login
{
	function generatePage(&$tpl, &$session, &$account)
	{
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Visitor/login.htm',__FILE__,__LINE__);
		
	}
}


