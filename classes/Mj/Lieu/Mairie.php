<?php
/** Gestion de l'interface de gestion de la mairie
*
* @package Mj
*/

class Mj_Lieu_Mairie
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/Mairie.htm',__FILE__,__LINE__);
	}
}
