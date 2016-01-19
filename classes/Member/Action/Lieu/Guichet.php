<?php
/** Gestion de l'interface d'un guichet automatique: Sélectionner une carte
*
* @package Member_Action
*/
class Member_Action_Lieu_Guichet
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
	
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
		
		//Afficher la liste des cartes de guichet
		$i=0;
		while( $item = $perso->getInventaire($i++))
			if($item instanceof Member_ItemCartebanque)
				$carteEnPossessionDuPerso[]  = $item;
		
		
		//Soumettre la liste des cartes au template uniquement s'il y a des cartes
		if (isset($carteEnPossessionDuPerso))
			$tpl->set('LIST_CARTE', $carteEnPossessionDuPerso);
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Guichet.htm',__FILE__,__LINE__);
	}
}

