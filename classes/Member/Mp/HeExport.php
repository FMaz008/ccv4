<?php
/**
 * Affichage de l'interface d'exportation du HE.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2010, Francois Mazerolle
 * @version 1.0
 * @package Mp
 */
class Member_Mp_HeExport
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{

		if($account->getMemberLevel()===0)
			return fctErrorMSG('Cette option est accessible aux Membres Plus seulement.');
		
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Mp/heExport.htm',__FILE__,__LINE__);
	}
}

