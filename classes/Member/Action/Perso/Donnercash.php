<?php
/** Gestion de l'interface d'une boutique
*
* @package Member_Action
*/
class Member_Action_Perso_Donnercash
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
	
		//Déclaration des variables pour cette action
		$pacost = 2; //PA par transaction
		
		//Créer le template
		$tpl->set('PA_COST',$pacost);
		$tpl->set('CASH',$perso->getCash());
		$tpl->set('PA',$perso->getPa());
		
		//Vérifier l'état du perso
		if(!$perso->isConscient())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
		
		
		// Liste des perso dans le lieu actuel
		$i=0; $e=0;
		while( $tmp = $perso->getLieu()->getPerso($perso, $i++))
			if($tmp->getId() != $perso->getId())
				$persoDansLeLieuActuel[$e++] = $tmp;
		if(isset($persoDansLeLieuActuel))
			$tpl->set('LIST_PERSO', $persoDansLeLieuActuel);
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Perso/DonnerCash.htm',__FILE__,__LINE__);
	}
}

