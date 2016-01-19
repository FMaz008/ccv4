<?php
/** Gestion de l'interface du jeu machine à sous
*
* @package Member_Action
*/
class Member_Action_Lieu_CasinoMachine
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
		
		
		
		//Rechercher le casino
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'casino'
				. ' WHERE casino_lieu=:nomTech'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':nomTech',	$perso->getLieu()->getNomTech(),	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		
		//Valider si le casino existe
		if (!$arr)
			return fctErrorMSG('Ce casino est actuellement innaccessible ou innexistante (' . $perso->getLieu()->getNomTech() . ').');
		
		$tpl->set('CASINO', $arr);
		
		
		
		
		
		
			
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/CasinoMachine.htm',__FILE__,__LINE__);
		
	}
}

