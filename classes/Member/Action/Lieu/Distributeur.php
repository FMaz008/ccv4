<?php
/** Gestion de l'interface d'une boutique
*
* @package Member_Action
*/
class Member_Action_Lieu_Distributeur
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
	
		//Déclaration des variables pour cette action
		$pacost = array();
		$pacost["achat"] = 10;
		//$pacost["nego"] = 20;
		
		//Créer le template
		$tpl->set("PA_COST",$pacost);
	
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
		
		
		//Lister l'inventaire du distributeur
		$query = 'SELECT i.*, db.*'
				. ' FROM ' . DB_PREFIX . 'lieu_distributeur as d'
				. ' LEFT JOIN ' . DB_PREFIX . 'producteur_inv as i'
					. ' ON (i.producteurId = d.producteurId)'
				. ' INNER JOIN ' . DB_PREFIX . 'item_db as db'
					. ' ON (db.db_id = i.itemDbId)'
				. ' WHERE lieuId=:lieuId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',		$perso->getLieu()->getId(),		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrItem = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		
		$tpl->set('INV_ITEM', $arrItem);
		
		
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
		
		
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Distributeur.htm',__FILE__,__LINE__);
	}
}
