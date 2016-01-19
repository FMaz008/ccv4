<?php
/** Gestion de l'interface de gestion des médias
*
* @package Mj
*/

class Mj_Lieu_Medias
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$mediaList = Member_MediaFactory::getExistMediaList();
		
		if(!empty($mediaList))
		{
			$tpl->set('MEDIA_LIST', $mediaList);
		}
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/Medias.htm',__FILE__,__LINE__);
	}
}

