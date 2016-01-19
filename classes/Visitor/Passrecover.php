<?php
/**
 * Page de demande de ré-initialisation de son mot de passe.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Visitor
 */
 
class Visitor_Passrecover
{
	public static function generatePage(&$tpl, &$session, &$account)
	{
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Visitor/passrecover.htm',__FILE__,__LINE__);
		
	}
}

