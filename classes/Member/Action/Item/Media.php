<?php
/** Gestion de l'interface de l'action Media: Afficher l'interface de l'action
*
* @package Member_Action
*/
class Member_Action_Item_Media
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		if(!$perso->isConscient())
			return fctErrorMSG('Vous n\'êtes pas en état d\'effectuer cette action.');
		
		$itemsMediaRecepteur = array();
		$itemsMediaEmetteur = array();
		$i=0;
		while( $item = $perso->getInventaire($i++))
			if($item instanceof Member_ItemMedia)
			{
				if($item->isRecepteur())
					$itemsMediaRecepteur[] = $item;
				else
					$itemsMediaEmetteur[] = $item;
			}
		
		$tpl->set('LIST_ITEMS_MEDIA_RECEPTEUR', $itemsMediaRecepteur);
		$tpl->set('LIST_ITEMS_MEDIA_EMETTEUR', $itemsMediaEmetteur);
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/Media.htm',__FILE__,__LINE__);
	}
}
