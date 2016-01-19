<?php

/** Gestion de l'interface de l'action Ordinateur dans le menu item
* Cette page sert à sélectionner le type d'action que l'on désire effectuer
* @package Member_Action
*/

class Member_Action_Item_Ordinateur
{

	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		//Vérifier l'état du perso
		if(!$perso->isConscient())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
		
		
		//Récupérer les items de type ordinateur que possède le PJ
		$i=0;
		$pcs = array();
		while( $item = $perso->getInventaire($i++))
			if($item instanceof Member_ItemOrdinateur)
				$pcs[] = $item;
		
		
		$tpl->set('LIST_PC', $pcs);
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/Ordinateur.htm',__FILE__,__LINE__);

	}

}
