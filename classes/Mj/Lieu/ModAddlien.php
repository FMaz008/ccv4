<?php
/** Affichage d'une ligne de lien vide
*
* @package Mj
*/
class Mj_Lieu_ModAddlien
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{

		$lien['id']=$_POST['divid'];
		$lien['from']=$_POST['lieu_tech'];
		$lien['to']='';
		$lien["icon"]='';
		$lien['pa']='0';
		$lien['cout']='0.00';
		$lien['protection']='';
		$lien['pass']='';
		$lien['bloque']='0';
		$lien['prefix']='al_';

		$tpl->set("lien",$lien);
		$tpl->set("ADD",true);

		$source = $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/AddmodLien.htm',__FILE__,__LINE__);
		die($source);

	}
}
