<?php
/** Affichage de la page de suppression du perso.
 *
 * @package Member
 * @subpackage Delete
 */
class Member_DelPerso2
{
	public static function generatePage(&$tpl, &$session, &$account)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if (!isset($_GET['id']) || !isset($_POST['yes']))
			return fctErrorMSG('Données requises manquantes. (cheat)');

		if($session->getVar('persoId') == $_GET['id'])
			$session->setVar('persoId', NULL);
		
		//Trouver les informations sur le perso / Valider s'il est authorisé à être modifié
		$query = 'SELECT id'
				. ' FROM ' . DB_PREFIX . 'perso'
				. ' WHERE id=:persoId'
					. ' AND userid=:userId'
					. ' AND inscription_valide="mod"'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$_GET['id'],		PDO::PARAM_INT);
		$prep->bindValue(':userId',		$account->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr === false)
			return fctErrorMSG('Ce personnage n\'existe pas, ne vous appartiend pas, ou n\'est pas en phase de refus. (cheat)');
		
		//Apeller la fonction qui gère la suppression de perso.
		Mj_Perso_Del::delete($_GET['id'], $account->getUser());
		

		
		//Augmenter le nombre de création de perso de 1
		$query = 'UPDATE ' . DB_PREFIX . 'account'
				. ' SET auth_creation_perso=auth_creation_perso+1'
				. ' WHERE id=:userId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':userId',	$account->getId(),		PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		
		//Retourner le template complété/rempli
		$tpl->set('PAGE', 'News');
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/redirect.htm',__FILE__,__LINE__);
		
	}
}

