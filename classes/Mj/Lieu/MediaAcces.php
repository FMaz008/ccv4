<?php
/** Gestion des accès média d'un lieu
 *
 * @package MJ
 */
 
 class Mj_Lieu_MediaAcces
 {
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		if(!isset($_GET['id']))
			return fctErrorMSG('Données requises manquantes.');
			
		//Trouver le lieu
		$lieu = Member_LieuFactory::createFromId($_GET['id']);
		
		//Lister les accès médias
		$mediaAcces = array();
		$i = 0;
		while($media = $lieu->getMediaAcces($i++))
		{
			$mediaAcces[] = $media;
		}
		
		$tpl->set('LIEU_TECH', $lieu->getNomTech());
		$tpl->set('LIEU_ID', $lieu->getId());
		$tpl->set('MEDIA_ACCES', $mediaAcces);
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/MediaAcces.htm',__FILE__,__LINE__);
	}
 }