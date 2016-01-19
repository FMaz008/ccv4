<?php
/** Action regarder médias pour un lieu
*
* @package Member_Action
*/
class Member_Action_Lieu_MediaListe
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		if(!$perso->isConscient())
			return fctErrorMSG('Vous n\'êtes pas en état d\'effectuer cette action.');
		
		//Lister tout les accès média du lieu
		$i=0;
		$mediaAccesEmetteur = array();
		$mediaAccesRecepteur = array();
		while( $media = $perso->getLieu()->getMediaAcces($i++))
		{
			if($media->isRecepteur())
				$mediaAccesRecepteur[] = $media;
			else
				$mediaAccesEmetteur[] = $media;
		}
		
		//Définir si le perso est gérant
		$gerant = false;
		if($perso->getLieu()->isgerant($perso))
			$gerant = true;
		
		$tpl->set('LIST_MEDIA_ACCES_RECEPTEUR', $mediaAccesRecepteur);
		$tpl->set('LIST_MEDIA_ACCES_EMETTEUR', $mediaAccesEmetteur);
		$tpl->set('GERANT', $gerant);
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/MediaListe.htm',__FILE__,__LINE__);
	}
}

