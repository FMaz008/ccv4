<?php
/** Gestion des radio: Créer un template pour gérer la fréquence de la radio
*
* @package Member_Action
*/
class Member_Action_Item_RadioMajFrequence
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
	
	
		if(!isset($_POST['idradio']))
			return fctErrorMSG('Radio ID attendu');
		
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
			die(fctErrorMSG('Cet objet ne vous appartient pas.'));
		
		
		$tpl->set('radio',$radio);

		//Retourner le template complété/rempli
		echo $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/RadioMajFrequence.htm',__FILE__,__LINE__);	
		die();
	}
}
