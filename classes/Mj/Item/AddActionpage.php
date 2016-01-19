<?php
/** Gestion de l'interface pour les items
*
* @package Mj
*/
class Mj_Item_AddActionpage
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{	//BUT: Démarrer un template propre à cette page
		//Générer la liste des actions associable à un lieu
		$i=0;
		$ACTION[$i]['tech']	= '';
		$ACTION[$i]['sel']	= true;
		$i++;
		
		//$ACTION[$i]['tech']	= 'a_gps';
		//$ACTION[$i]['sel']	= false;
		//$i++;
		
		$ACTION[$i]['tech']	= 'Radios';
		$ACTION[$i]['sel']	= false;
		$i++;
		
		$ACTION[$i]['tech']	= 'Telephoner';
		$ACTION[$i]['sel']	= false;
		$i++;
		
		$ACTION[$i]['tech']	= 'Ordinateur';
		$ACTION[$i]['sel']	= false;
		$i++;
		
		$ACTION[$i]['tech']	= 'Media';
		$ACTION[$i]['sel']	= false;
		$i++;
		
		$ACTION[$i]['tech']	= 'Menotter';
		$ACTION[$i]['sel']	= false;
		$i++;
		
		$ACTION[$i]['tech']	= 'Sac';
		$ACTION[$i]['sel']	= false;
		$i++;
		
		
		$tpl->set('ACTION',$ACTION);
		$tpl->set('ACTION_CAPTION','');
		$tpl->set('ACTION_IDX',$_POST['divid']);
		
		$source = $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Item/Add_Actionpage.htm');
		die($source);
	}
}
?>
