<?php
/**
 * Affichage des infos IRC.
 *
 * @author Quentin Virol
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Visitor
 */
 
class Visitor_Irc
{
	public static function generatePage(&$tpl, &$session, &$account)
	{
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Visitor/irc.htm',__FILE__,__LINE__);
		
	}
}

