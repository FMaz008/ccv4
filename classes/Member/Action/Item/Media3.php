<?php
/** Gestion de l'interface de l'action Média : accéder aux médias
*
* @package Member_Action
*/
class Member_Action_Item_Media3
{ 
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$errorUrl = '?popup=1&amp;m=Action_Item_Media';
		$paCost = 0;
		
		if(!$perso->isConscient())
			return fctErrorMSG('Vous n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
			
		//Pas assez de PA
		if($perso->getPa() <= $paCost)
			return fctErrorMSG('Vous n\'avez pas assez de PA pour effectuer cette action.', $errorUrl);
		
		//Vérifications du post
		if(!isset($_POST['title']))
			return fctErrorMSG('Vous n\'avez pas sélectionné d\'émission.', $errorUrl);
			
		if(empty($_POST['message']))
			return fctErrorMSG('Vous devez écrire un message.', $errorUrl);
			
		if($_POST['title'] == 'new' && empty($_POST['new_title']))
			return fctErrorMSG('Vous devez entrer un nom d\'émission.', $errorUrl);
			
		//Récupérer les informations sur l'item media
		$item = Member_ItemFactory::createFromInvId($_POST['item']);
		if($item == false)
			return fctErrorMsg('Erreur : l\'item selectionné n\'est pas supporté.', $errorUrl);
			
		if($item->getMediaType() != 'tous')
			if($item->getMediaType() != $_POST['mediaType'])
				return fctErrorMsg('Le type de média selectionné n\'est pas accessible par cet objet.', $errorUrl);
				
		$item->writeInMedia($_POST['mediaType'], $_POST['channel'], $_POST[$_POST['title'] . '_title'], $_POST['message']);
		
		$perso->changePa('-', $paCost);
		$perso->setPa();
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}