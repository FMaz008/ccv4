<?php
/** Gestion des accès média dans un lieu par son propriétaire
*
* @package Member_Action
*/
class Member_Action_Lieu_MediaAccesAdmin
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$errorUrl = '?popup=1&amp;m=Action_Lieu_MediaListe';
		
		//Valider si le perso est gérant
		if(!$perso->getLieu()->isGerant($perso))
			return fctErrorMSG('Vous devez être propriétaire du lieu pour pouvoir l\'administrer.', $errorUrl, array('perso' => $perso, 'lieu' => $perso->getLieu()));
			
		//Lister les accès média
		$i = 0;
		$mediaAcces = array();
		while($media = $perso->getLieu()->getMediaAcces($i++))
		{
			$mediaAcces[] = $media;
		}
		
		if(isset($_POST['save']))
		{
			$e = 0;
			while($e < count($mediaAcces))
			{
				$mediaAcces[$e]->changeChannelId($_POST[$mediaAcces[$e]->getId() . '_channelId']);
				$mediaAcces[$e]->setChannelId();
				$e++;
			}
		}
		
		$tpl->set('MEDIA_ACCES', $mediaAcces);
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/MediaAccesAdmin.htm',__FILE__,__LINE__);
	}
}