<?php
/** Gestion de l'interface d'un guichet automatique: Sélectionner une carte
*
* @package Member_Action
*/
class Member_Action_Lieu_Mairie
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
	
		//Vérifier l'état du perso
		if(!$perso->isNormal())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
		
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Mairie.htm',__FILE__,__LINE__);
	}
}

