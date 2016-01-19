<?php
/** Gestion de l'interface de l'action Média : accéder aux médias
*
* @package Member_Action
*/
class Member_Action_Item_Media2
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
		if(!isset($_POST['mediaSelected']))
			return fctErrorMSG('Vous n\'avez pas sélectionné d\'objet.', $errorUrl);
			
		if(empty($_POST[$_POST['mediaSelected'] . '_channelSelected']))
			return fctErrorMSG('Vous n\'avez pas choisi de canal.', $errorUrl);
			
		//Récupérer les informations sur l'item media
		$item = Member_ItemFactory::createFromInvId($_POST['mediaSelected']);
		if($item == false)
			return fctErrorMsg('Erreur : l\'item selectionné n\'est pas supporté.', $errorUrl);
			
		if($item->getMediaType() != 'tous')
			if($item->getMediaType() != $_POST[$_POST['mediaSelected'] . '_typeSelected'])
				return fctErrorMsg('Le type de média selectionné n\'est pas accessible par cet objet.', $errorUrl);
		
		$mediaMsg = $item->getMediaContenu($_POST[$_POST['mediaSelected'] . '_typeSelected'], $_POST[$_POST['mediaSelected'] . '_channelSelected']);
		
		$mediaReception = $item->isRecepteur();
		$mediaEmission = $item->isEmetteur();
		
		//Si on veut regarder les média : retirer les PA
		if($item->isRecepteur())
		{
			$perso->changePa('-', $paCost);
			$perso->setPa();
		}
		
		$tpl->set('MEDIA_RECEPTION', $mediaReception);
		$tpl->set('MEDIA_EMISSION', $mediaEmission);
		$tpl->set('MEDIA_MESSAGE', $mediaMsg);
		$tpl->set('PERSO', $perso);
		$tpl->set('CANAL', $_POST[$_POST['mediaSelected'] . '_channelSelected']);
		$tpl->set('ITEM', $item);
		if($_POST[$_POST['mediaSelected'] . '_typeSelected'] = 'tele')
			$typeAffichable = 'télé';
		else
			$typeAffichable = 'radio';
		$tpl->set('MEDIA', $typeAffichable);
		$tpl->set('MEDIA_TYPE', $_POST[$_POST['mediaSelected'] . '_typeSelected']);
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/Media2.htm',__FILE__,__LINE__);
	}
}
