<?php
/**
 * Génère le contenu de l'onglet Dé
 * @package Mj
 * @subpackage Ajax
 */

class Mj_TabDe
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
		echo $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/tabDe.htm');
		die();
	}
}

