<?php
/** Gestion d'une boutique par son propriétaire
*
* @package Member_Action
*/
class Member_Action_Lieu_BoutiqueAdmin
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		$errorUrl = '?popup=1&amp;m=Action_Lieu_Boutique';
		
		
		//Valider si le lieu actuel est une boutique
		if(!$perso->getLieu()->isBoutique())
			return fctErrorMSG('Ce lieu n\'est pas une boutique.');
			
			
		//Définir les accès d'administration
		if(!$perso->getLieu()->isGerant($perso))
			return fctErrorMSG('Vous devez être propriétaire du lieu pour pouvoir l\'administrer.', $errorUrl, array('perso' => $perso, 'lieu' => $lieu));
		
		
			
		$tpl->set('CASH_CAISSE', $perso->getLieu()->getBoutiqueCash());
		
		
		
		//LISTER TOUT LES ITEMS EN VENTE DANS LA BOUTIQUE
		$i=0;
		$items=array();
		while( $item = $perso->getLieu()->getBoutiqueInventaire($i++))
			$items[$i] = $item;
		$tpl->set('INV_BOUTIQUE', $items);
		
		
		
		//LISTER TOUT LES ITEMS QUE LE PERSO POSSÈDE SUR LUI
		$i=0;
		$items=array();
		while( $item = $perso->getInventaire($i++))
			if(!($item instanceof Member_ItemDrogueDrogue)) //Pourquoi les drogues sont-elles excluses ?
				$items[] = $item;
		$tpl->set('INV_PERSO', $items);
		
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/BoutiqueAdmin.htm',__FILE__,__LINE__);
	}
}

