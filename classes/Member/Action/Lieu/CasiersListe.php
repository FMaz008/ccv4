<?php
/** Gestion d'une boutique par son propriétaire
*
* @package Member_Action
*/
class Member_Action_Lieu_CasiersListe
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		//Valider l'état du perso
		if (!$perso->isConscient())
			return fctErrorMSG('Vous n\'êtes pas en état d\'effectuer cette action.');
		
		//Lister tout les casiers du lieu
		$i=0;
		$CASIERS = array();
		while( $casier = $perso->getLieu()->getCasiers($i++))
			$CASIERS[] = $casier;
		
		
		//Passer la liste des casiers au template uniquement si elle comprend au moins 1 casier
		if(count($CASIERS)>0)
			$tpl->set('CASIERS', $CASIERS);
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/CasiersList.htm',__FILE__,__LINE__);
	}
}

