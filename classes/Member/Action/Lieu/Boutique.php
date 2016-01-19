<?php
/** Gestion de l'interface d'une boutique
*
* @package Member_Action
*/
class Member_Action_Lieu_Boutique
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
	
		//Déclaration des variables pour cette action
		$pacost = array();
		$pacost['achat'] = 5;
		$pacost['nego'] = 20;
		$pacost['vol'] = 40;
		
		//Créer le template
		$tpl->set('PA_COST',$pacost);
	
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
		
		
		//Valider si le lieu actuel est une boutique
		if(!$perso->getLieu()->isBoutique())
			return fctErrorMSG('Ce lieu n\'est pas une boutique ou n\'a aucun propriétaire attitré. Contactez un MJ.');
		
		$tpl->set('CAN_VOL', $perso->getLieu()->canVol());
		
		
		//Lister l'inventaire de la boutique
		//LISTER TOUT LES ITEMS EN VENTE DANS LA BOUTIQUE
		$i=0; $items=array();
		while( $item = $perso->getLieu()->getBoutiqueInventaire($i++))
			if ($item->getBoutiquePrixVente()>=0)
				$items[$i] = $item;
		$tpl->set('INV_BOUTIQUE', $items);
		
		
		//Lister les cartes bancaires + les items en inventaire que la boutique se propose d'acheter
		$i=0;
		$cartes=array();
		$inv=array();
		while( $item = $perso->getInventaire($i++))
		{
			//Faire une liste des cartes bancaires
			if($item instanceof Member_ItemCartebanque)
				$cartes[]  = $item;
			
			//Faire une liste des items que la boutique propose d'acheter
			$j=0;
			while( $item2 = $perso->getLieu()->getBoutiqueInventaire($j++))
				if($item->getDbId() == $item2->getDbId() && $item2->getBoutiquePrixAchat()>=0){
					$inv[] = array('inv'=>$item,'boutique'=>$item2);
					break;
				}
		}
		$tpl->set('INV_PERSO', $inv);
		
		
		//Si la boutique supporte le paiement direct
		$noBanque = $perso->getLieu()->getBoutiqueNoBanque();
		$noCompte = $perso->getLieu()->getBoutiqueNoCompte();
		if(!empty($noBanque) && !empty($noCompte))
		{
			$tpl->set('PAIEMENT_DIRECT', true);
			
			if (isset($cartes))
				$tpl->set('CARTES', $cartes);
		}
		
		
		
		
		//Définir les accès d'administration
		if($perso->getLieu()->isGerant($perso))
			$tpl->set('admin', true);
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Boutique.htm',__FILE__,__LINE__);
	}
}
