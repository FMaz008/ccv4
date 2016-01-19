<?php
/**
 * Affichage de l'interface d'abonnement.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Mp
 */
class Member_Mp_Abo1
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		
		$tpl->set('ACCOUNT_ID', $account->getId());
		$tpl->set('ACCOUNT_USER', $account->getUser());
		$tpl->set('MP_LVL', $account->getMemberLevel());
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Mp/abo1.htm',__FILE__,__LINE__);
	}
}

