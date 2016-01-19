<?php
/** Historique des transactions d'une boutique
*
* @package Member_Action
*/
class Member_Action_Lieu_BoutiqueHistorique
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
		
		$historique = $perso->getLieu()->getBoutiqueHistorique();
		
		if($historique != null)
			$tpl->set('HISTORIQUE_LIST', $historique);
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/BoutiqueHistorique.htm',__FILE__,__LINE__);
	}
}

