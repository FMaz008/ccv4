<?php
/**
 * Affichage de la FAQ.
 *
 * @author Quentin Virol
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Visitor
 */
 
class Visitor_Faq
{
	public static function generatePage(&$tpl, &$session, &$account)
	{

		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Visitor/faq.htm',__FILE__,__LINE__);
		
	}
}

