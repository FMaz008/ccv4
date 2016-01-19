<?php
/** Gestion de l'interface de l'action Média : accéder aux médias depuis un lieu
*
* @package Member_Action
*/
class Member_Action_Lieu_MediaAcces
{ 
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$errorUrl = '?popup=1&amp;m=Action_Lieu_MediaListe';
		$paCost = 0;
		
		if(!$perso->isConscient())
			return fctErrorMSG('Vous n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
			
		//Pas assez de PA
		if($perso->getPa() <= $paCost)
			return fctErrorMSG('Vous n\'avez pas assez de PA pour effectuer cette action.', $errorUrl);
		
		//Vérifications du post
		if(!isset($_POST['mediaSelected']))
			return fctErrorMSG('Vous n\'avez pas sélectionné d\'objet.', $errorUrl);
			
		//Récupérer les informations sur l'accès média
		$i = 0;
		$mediaAcces = false;
		while($media = $perso->getLieu()->getMediaAcces($i++))
		{
			if($media->getId() == $_POST['mediaSelected'])
			{
				$mediaAcces = $media;
				break;
			}
		}
		if($mediaAcces == false)
			return fctErrorMsg('Erreur : l\'item selectionné n\'existe pas.', $errorUrl);
		
		$mediaMsg = $mediaAcces->getMediaContenu();
		
		$mediaReception = $mediaAcces->isRecepteur();
		$mediaEmission = $mediaAcces->isEmetteur();
		
		//Si on veut regarder les média : retirer les PA
		if($mediaReception)
		{
			$perso->changePa('-', $paCost);
			$perso->setPa();
		}
		
		$tpl->set('MEDIA_RECEPTION', $mediaReception);
		$tpl->set('MEDIA_EMISSION', $mediaEmission);
		$tpl->set('MEDIA_MESSAGE', $mediaMsg);
		$tpl->set('PERSO', $perso);
		$tpl->set('MEDIA_ACCES', $mediaAcces);
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/MediaAcces.htm',__FILE__,__LINE__);
	}
}
