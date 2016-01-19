<?php
/** Gestion de la création d'un compte de banque
*
* @package Member_Action
*/
class Member_Action_Lieu_BanqueCompteAdd
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		
		//Rechercher la banque
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque'
				. ' WHERE banque_lieu=:nomTech'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':nomTech',		$perso->getLieu()->getNomTech(),	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		//Valider si la banque existe
		if ($arr === false)
			return fctErrorMSG('Cette banque n\'existe pas (' . $perso->getLieu()->getNomTech() . ').');
		
		//Instancier la banque
		$banque = new Member_Banque($arr);
		
		
		//Passer objets au template
		$tpl->set('PA', $perso->getPa());
		$tpl->set('CASH', $perso->getCash());
		$tpl->set('BANQUE', $banque);
		$tpl->set('BANK_ACCOUNT_NAME',	$perso->getNom());
		
		
		//Afficher la page
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Banque_compte_add.htm',__FILE__,__LINE__);
	}
}
