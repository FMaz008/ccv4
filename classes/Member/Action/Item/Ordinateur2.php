<?php

/** Gestion de l'interface de l'action Ordinateur dans le menu item
* Cette page sert à sélectionner le type d'action que l'on désire effectuer
* @package Member_Action
*/

class Member_Action_Item_Ordinateur2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?popup=1&amp;m=Action_Item_Ordinateur';
		
		//Vérifier l'état du perso
		if(!$perso->isConscient())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
		
		
		if(!is_numeric($_GET['id']))
			return fctErrorMSG('Id invalide.', $errorUrl);	
		
		//Trouver l'item demandé
		$i=0;
		$pc = false;
		while( $item = $perso->getInventaire($i++))
		{
			if($item->getInvId()==$_GET['id'])
			{
				$pc = $item;
				break;
			}
		}
		
		//Valider si on possède l'item
		if($pc===false)
			return fctErrorMSG('Cet item ne vous appartient pas.', $errorUrl);
		
		//Valider si l'item est de type ordinateur
		if(!$pc instanceof Member_ItemOrdinateur)
			return fctErrorMSG('Cet item n\'est pas un appareil informatique.', $errorUrl);
		
		
		
		
		//Rechercher toutes les cartes mémoires
		$i=0; $e=0;
		$cm=array();
		while( $item = $perso->getInventaire($i++))
			if($item instanceof Member_ItemCartememoire)
				$cm[$e++] = $item;
	
	
	
		$tpl->set('INTERNET',	$pc->isInternetCapable());
		$tpl->set('MCREAD',		$pc->getMcRead());
		$tpl->set('NOM',		$pc->getNom());
		$tpl->set('PC_ID',		$pc->getInvId());
		$tpl->set('CARTES',		$cm);
		$tpl->set('PC_IS_CRYPT',$pc->isCrypt());
		
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/Ordinateur2.htm',__FILE__,__LINE__);

	}

}

