<?php
/** Gestion du travail à la production
*
* @package Member_Action
*/
class Member_Action_Lieu_Producteur
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Valider l'état du perso
		if (!$perso->isNormal())
			return fctErrorMSG('Vous n\'êtes pas en état d\'effectuer cette action.');
		
		if ($perso->getMenotte())
			return fctErrorMSG('Vous ne pouvez pas travailler en étant menotté.');
		
		
		//Trouver les informations sur le producteur
		$query = 'SELECT *'
				. ' FROM `' . DB_PREFIX . 'producteur`'
				. ' WHERE lieuId=:lieuId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',		$perso->getLieu()->getId(),		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr === false)
			return fctErrorMSG('Ce lieu n\'est pas un producteur.');
		
		
		$tpl->set('PROD', $arr);
		$tpl->set('PERSO', $perso);
		
		
		//Retourner le template bâti.
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Producteur.htm',__FILE__,__LINE__);
	}
}


