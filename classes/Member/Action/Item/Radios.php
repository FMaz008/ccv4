<?php
/** Gestion des radio: Affichag edes radios dispo
*
* @package Member_Action
*/
class Member_Action_Item_Radios
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if(!$perso->isConscient())
			return fctErrorMSG('Vous n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
		
		
		$i=0;
		while( $item = $perso->getInventaire($i++))
			if($item instanceof Member_ItemRadio)
				$radios[] = $item;
		
	
	
		$tpl->set('LIST_RADIOS', $radios);
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/Radios.htm',__FILE__,__LINE__);	
	}
}

