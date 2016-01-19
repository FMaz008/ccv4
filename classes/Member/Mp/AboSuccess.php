<?php
/**
 * Affichage de la page de confirmation de l'abonnement.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Mp
 */
class Member_Mp_AboSuccess
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{	//BUT: Démarrer un template propre à cette page
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Mp/aboSuccess.htm',__FILE__,__LINE__);
	}
}

