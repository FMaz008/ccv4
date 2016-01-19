<?php
/** Gestion des radio: Traitement de l'appel  radio
*
* @package Member_Action
*/
class Member_Action_Item_Radios2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?popup=1&amp;m=Action_Item_Radios';
		
		if(!$perso->isConscient())
			return fctErrorMSG('Vous n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
		
		
		if(!isset($_POST['radio']))
			return fctErrorMSG('Aucune radio sélectionnée', $errorUrl);
		
		if( $_POST['message'] == NULL)
			return fctErrorMSG('Aucun son n\'a été enregistré', $errorUrl);
			
			
		//On récupère la radio associée à l'id transmis
		$i=0;
		while( $item = $perso->getInventaire($i++))
		{
			if($item instanceof Member_ItemRadio && ($item->getInvId() == $_POST['radio']) )
			{
				$radio = $item;
				break;
			}
		}
		if(!isset($radio))
			return fctErrorMSG('Cet item ne vous appartient pas.', $errorUrl);
		
		
		$radio->utiliser($perso,$_POST['message']);
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}

