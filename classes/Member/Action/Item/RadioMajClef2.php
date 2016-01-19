<?php
/** AJAX: Regler la clef de cryptage de la radio
*
* @package Member_Action
* @subpackage Ajax
*/
class Member_Action_Item_RadioMajClef2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		$i=0;
		while( $item = $perso->getInventaire($i++))
		{
			if($item instanceof Member_ItemRadio && ($item->getInvId() == $_POST['idradio']))
			{
				$radio = $item;
				break;
			}
		}
		if(!isset($radio))
			die('Cet objet ne vous appartient pas.');
		
		if(empty($_POST['clef']))
			$_POST['frequence'] = 0;
		
		if(!is_numeric($_POST['clef']))
			die('Cette clef n\'est pas numérique');
		
		$radio->majClef($_POST['clef']);
		
		die( $radio->getInvId() . '|' . $_POST['clef']);
		
	}
}
