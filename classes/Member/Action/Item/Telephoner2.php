<?php
/** Gestion de l'interface de l'action Téléphoner: Envoyer le message
*
* @package Member_Action
*/
class Member_Action_Item_Telephoner2
{ 
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?popup=1&amp;m=Action_Item_Telephoner';
		
		if(!$perso->isConscient())
			return fctErrorMSG('Vous n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
		
		
		//Vérifications du post
		if(!isset($_POST['telephone']))
			return fctErrorMSG('Vous n\'avez pas sélectionné de téléphone.', $errorUrl);
			
		if(empty($_POST['numero_destinataire']))
			return fctErrorMSG('Vous n\'avez pas composé de numéro de téléphone', $errorUrl);
			
		if(empty($_POST['message']))
			return fctErrorMSG('Vous n\'avez pas entré de message', $errorUrl);
		
		
		//récup des info  du l'appeleur
		$i=0;
		while( $item = $perso->getInventaire($i++))
		{
			if($item instanceof Member_ItemTelephone)
			{
				if($item->getInvId() == $_POST['telephone'])
				{
					$telephoneAppeleur = $item;
					break;
				}
			}
		}	
		
		try
		{
			$telephoneAppeleur->envoyerMessage($_POST['numero_destinataire'], $_POST['message'],$_POST['anonyme']);
		}
		catch(GameException $e)
		{
			return fctErrorMSG($e->getMessage(), $errorUrl);
		}
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}
