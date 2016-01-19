<?php
/** Gestion de l'interface d'accès à un ordinateur fixe
*
* @package Member_Action
*/
class Member_Action_Lieu_OrdinateurFixe
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		
		//Valider si le joueur à accès à Internet
		if(Member_Action_Item_Navigateur::checkAccess($perso)===false)
			return fctErrorMSG('Vous n\'avez pas accès à Internet.');
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/OrdinateurFixe.htm',__FILE__,__LINE__);
	}
}

