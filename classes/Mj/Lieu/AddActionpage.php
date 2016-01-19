<?php
/** AJAX: Gestion de la liste des action rataché à des lieux
*
* @package Mj
* @subpackage Ajax
*/
class Mj_Lieu_AddActionpage
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{	
		//Générer la liste des actions associable à un lieu
		
		$arrVide = array('url'=>'', 'nom'=>'', 'config_url'=>null, 'sel'=>true);
		$ACTION = array_merge(array($arrVide), Mj_Lieu::getAllLinkedActions());

		$tpl->set('ACTION',$ACTION);
		$tpl->set('ACTION_IDX',$_POST['divid']);
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/AddmodActionpage.htm');
	}
}

