<?php
/** Gestion des radio: Regler la clef de cryptage de la radio
*
* @package Member_Action
*/
class Member_Action_Item_RadioMajClef
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
			return fctErrorMSG('Cet objet ne vous appartient pas.');
		
		
		
		$tpl->set('radio',$radio);
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/RadioMajClef.htm',__FILE__,__LINE__);	
	}
}
