<?php
/** Gestion d'un accès média (page de modification)
*
* @package Mj
*/

class Mj_Lieu_MediaAccesMod
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$tpl->set('LIEU_ID', $_POST['LIEU_ID']);
	
		if(!isset($_POST['id_mediaAcces']))
			return fctErrorMSG('Données requises manquantes.');
		
		//## Sauvegarder les modifications ?
		if(isset($_POST['save']))
		{
			return self::save();
		}
		
		//## Afficher les informations dans le formulaire de modification
		
		//Fetcher les informations sur le casier à modifier
		$lieu = Member_LieuFactory::createFromId($_POST['LIEU_ID']);
		
		$i = 0;
		while($media = $lieu->getMediaAcces($i++))
		{
			if($media->getId() == $_POST['id_mediaAcces'])
				break;
		}
		
		if($media === false)
			return fctErrorMSG('L\'accès média #' . $_POST['id_mediaAcces'] . ' n\'existe pas.');
		
		$tpl->set('MEDIA_ACCES', $media);
		$tpl->set('LIEU_TECH', $lieu->getNomTech());
		$tpl->set('SHOWID', true);
		$tpl->set('ACTIONTYPETXT', 'Modifier');
		$tpl->set('SUBMITNAME', 'Mod');
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/MediaAccesAddmod.htm',__FILE__,__LINE__);
	}
	
	private static function save()
	{
		$oldLieu = Member_LieuFactory::createFromId($_POST['LIEU_ID']);
		
		$i = 0;
		while($media = $oldLieu->getMediaAcces($i++))
		{
			if($media->getId() == $_POST['id_mediaAcces'])
				break;
		}
		
		if($media === false)
			return fctErrorMSG('L\'accès média #' . $_POST['id_mediaAcces'] . ' n\'existe pas.');
		
		if(empty($_POST['nom']))
			return fctErrorMSG('Vous devez donner un nom à l\'accès média.');
		if(empty($_POST['channelId']))
			return fctErrorMSG('Vous devez entrer un canal.');
			
		if(!is_numeric($_POST['lieuId']))
		{
			$newLieu = Member_LieuFactory::createFromNomTech($_POST['lieuId']);
			$_POST['lieuId'] = $newLieu->getId();
		}
		
		$media->changeNom($_POST['nom']);
		$media->changeMediaType($_POST['mediaType']);
		$media->changeChannelId($_POST['channelId']);
		$media->changeInteractionType($_POST['interactionType']);
		$media->changeLieuId($_POST['lieuId']);
		$media->setNom();
		$media->setMediaType();
		$media->setChannelId();
		$media->setInteractionType();
		$media->setLieuId();
		
		die("<script type=\"text/javascript\">location.href='?mj=Lieu_MediaAcces&id=" . $_POST['lieuId'] . "';</script>");
	}
}