<?php
/** AJAX: Mettre à jour la fréquence de la radio
*
* @package Member_Action
* @subpackage Ajax
*/
class Member_Action_Item_RadioMajFrequence2
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
		
		if(empty($_POST['frequence']))
			$_POST['frequence'] = 0;
		
		if(!is_numeric($_POST['frequence']))
			die('Cette fréquence n\'est pas numérique');
		
		$radio->majFrequence($_POST['frequence']);
		
		die( $radio->getInvId() . '|' . $_POST['frequence']);
		
	}
}
