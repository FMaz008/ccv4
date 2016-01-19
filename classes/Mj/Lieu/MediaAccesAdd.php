<?php
/** Gestion d'un accès média (page d'ajout)
*
* @package Mj
*/

class Mj_Lieu_MediaAccesAdd
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{			
		$tpl->set('LIEU_ID', $_POST['LIEU_ID']);
	
		//## Sauvegarder les modifications ?
		if(isset($_POST['save']))
		{
			self::save();
		}
			
		//## Afficher les informations dans le formulaire de modification
		
		//Trouver le nom technique de l'ID de lieu
		$lieu = Member_LieuFactory::createFromId($_POST['LIEU_ID']);
			
		if ($lieu === false)
			return fctErrorMSG('Ce lieu n\'existe pas.');
			
		$tpl->set('LIEU_TECH', $lieu->getNomTech());
		$tpl->set('LIEU_ID', $lieu->getId());
		$arr['lieuid'] = $lieu->getId();
		$arr['id'] = 0;
		$arr['nom'] = '';
		$arr['mediaType'] = 'tele';
		$arr['canalId'] = 0;
		$arr['interactionType'] = 0;
		$mediaAcces = new Member_MediaAcces($arr);
		$tpl->set('MEDIA_ACCES', $mediaAcces);
		
		$tpl->set('SHOWID', false);
		$tpl->set('ACTIONTYPETXT', 'Ajouter');
		$tpl->set('SUBMITNAME', 'Add');
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/MediaAccesAddmod.htm',__FILE__,__LINE__);
	}
	
	
	
	private static function save()
	{	
		$lieuSave = Member_LieuFactory::createFromId($_POST['lieuId']);
		
		$lieuSave->addMediaAcces($_POST['nom'], $_POST['mediaType'], $_POST['channelId'], $_POST['interactionType']);
		
		die("<script type=\"text/javascript\">location.href='?mj=Lieu_MediaAcces&id=" . $_POST['lieuId'] . "';</script>");
	}
}