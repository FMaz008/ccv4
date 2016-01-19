<?php
/** Gestion de l'interface de l'action Téléphoner: Afficher l'interface de l'action
*
* @package Member_Action
*/
class Member_Action_Item_Telephoner
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if(!$perso->isConscient())
			return fctErrorMSG('Vous n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
		
		
		$i=0;
		while( $item = $perso->getInventaire($i++))
			if($item instanceof Member_ItemTelephone)
				$telephonesDuPerso[] = $item;
		
		
	$tpl->set('LIST_TELEPHONES', $telephonesDuPerso);
	//Retourner le template complété/rempli
	return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/Telephoner.htm',__FILE__,__LINE__);
	}
}
