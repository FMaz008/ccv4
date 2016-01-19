<?php
/**
 * Affichage de la page d'échec de l'abonnement.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Mp
 */
class Member_Mp_AboCancel
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Mp/aboCancel.htm',__FILE__,__LINE__);
	}
}

