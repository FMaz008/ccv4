<?php
/** Suppression d'un accès média
*
* @package Mj
*/

class Mj_Lieu_MediaAccesDel
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{	
		$lieu = Member_LieuFactory::createFromId($_POST['LIEU_ID']);
		
		if(!isset($_POST['id_mediaAcces']))
			return fctErrorMSG('Vous devez sélectionner un accès média.');
		
		$i = 0;
		while($media = $lieu->getMediaAcces($i++))
		{
			if($media->getId() == $_POST['id_mediaAcces'])
				break;
		}
		
		if($media === false)
			return fctErrorMSG('L\'accès média #' . $_POST['id_mediaAcces'] . ' n\'existe pas.');
			
		$lieu->delMediaAcces($media);
		
		if(isset($_POST['LIEU_ID']))
			die("<script type=\"text/javascript\">location.href='?mj=Lieu_MediaAcces&id=" . $_POST['LIEU_ID'] . "';</script>");
		else
			die("Supprimé.");
	}
}