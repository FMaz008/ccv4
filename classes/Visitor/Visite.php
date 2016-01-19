<?php
/**
 * Affichage de la visite du site.
 *
 * @author Quentin Virol
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Visitor
 */
 
class Visitor_Visite
{
	public static function generatePage(&$tpl, &$session, &$account)
	{
	
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Visitor/visite.htm',__FILE__,__LINE__);
		
	}
}

