<?php
/** Gestion de l'interface pour créer un site sur Domnet
* Cette page est incluse par Member_Action_Item_Navigateur
* @package Member_Action
*/
class Member_Action_Item_NavigateurAddsite
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		
		
			
		
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Item/NavigateurDomnetAddsite.htm',__FILE__,__LINE__);
	}
}

