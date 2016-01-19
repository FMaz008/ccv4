<?php
/** Gestion d'un laboratoire de drogue
*
* @package Member_Action
*/
class Member_Action_Lieu_LaboDrogue
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page

		$coutPa = 50;
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
		
		
		//Valider si le lieu actuel est un labo
		if(!$perso->getLieu()->isLaboDrogue())
			return fctErrorMSG('Ce lieu n\'est pas un laboratoire de drogue.');
		
		
		
		
		//LISTER TOUTES LES DROGUES_SUBSTANCS QUE LE PERSO POSSÈDE SUR LUI
		$i=0;
		$items=array();
		while( $item = $perso->getInventaire($i++))
			if($item instanceof Member_ItemDrogueSubstance)
				$items[] = $item;
		$tpl->set('INV_PERSO', $items);
		
		$tpl->set('COUT_PA', $coutPa);
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/LaboDrogue.htm',__FILE__,__LINE__);
	}
}

