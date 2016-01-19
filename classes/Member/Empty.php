<?php
/** Retourner une page vide.
 *
 * @package Member
 */
class Member_Empty
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/empty.htm',__FILE__,__LINE__);
	}
}


