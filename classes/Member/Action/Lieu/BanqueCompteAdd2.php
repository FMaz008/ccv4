<?php
/** Gestion de la création d'un compte de banque
*
* @package Member_Action
*/
class Member_Action_Lieu_BanqueCompteAdd2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		$errorUrl = '?popup=1&amp;m=Action_Lieu_BanqueCompteAdd';
		$coutPa = 1;

		if($perso->getPa() <= $coutPa)
			return fctErrorMSG('Vous n\'avez pas assez de PA pour effectuer cette action.', $errorUrl);

		if(empty($_POST['nom']))
			return fctErrorMSG('Vous devez associer un nom avec le compte.', $errorUrl);

		if(empty($_POST['nip']))
			return fctErrorMSG('Vous devez associer un nip avec le compte.', $errorUrl);

		if(!is_numeric($_POST['nip']))
			return fctErrorMSG ('Le nip doit être numérique.', $errorUrl);

		if($_POST['nip'] < 0)
			return fctErrorMSG ('Le nip est incorrect.', $errorUrl);
		
		//Rechercher la banque
		$query = 'SELECT *'
				. '  FROM ' . DB_PREFIX . 'banque'
				. ' WHERE banque_lieu=:nomTech;';
		$prep = $db->prepare($query);
		$prep->bindValue(':nomTech',		$perso->getLieu()->getNomTech(),		PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		//Valider si la banque existe
		if ($arr === false)
			return fctErrorMSG('Cette banque n\'existe pas (' . $perso->getLieu()->getNomTech() . ').');
			
			
		//Instancier la banque
		$banque = new Member_Banque($arr);
		
		
		//Vérifier si la personne à assez d'argent sur elle pour ouvrir un compte
		if ($perso->getCash() < $banque->getFraisOuverture ())
			return fctErrorMSG('Vous n\'avez pas assez d\'argent pour effectuer cette action.', $errorUrl);
		
		
		//Effectuer le paiement ( Cash + PA)
		$perso->changeCash('-', $banque->getFraisOuverture ());
		$perso->setCash();
		
		$perso->changePa('-', $coutPa);
		$perso->setPa();
		
		
		//Créer le compte
		$compte_no = Member_BanqueCompte::generateAccountNo();
		$query = 'INSERT INTO `' . DB_PREFIX . 'banque_comptes`'
				. ' (`compte_idperso` , `compte_nom` , `compte_banque` , `compte_compte` , `compte_cash`, `compte_nip`)'
				. ' VALUES'
				. ' (:persoId, :nom, :banque, :compte, 0, :nip);';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$perso->getId(),PDO::PARAM_INT);
		$prep->bindValue(':banque',		$banque->getNoBanque(),		PDO::PARAM_STR);
		$prep->bindValue(':compte',		$compte_no,		PDO::PARAM_STR);
		$prep->bindValue(':nom',		$_POST['nom'],	PDO::PARAM_STR);
		$prep->bindValue(':nip',		$_POST['nip'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		$compteId = $db->lastInsertId();
		
		
		//Rechercher le compte afin d'y faire des opérations.
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE	compte_id=:compteId'
					. ' AND compte_idperso=:persoId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compteId',		$compteId,			PDO::PARAM_INT);
		$prep->bindValue(':persoId',		$perso->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		//Valider si le compte existe
		if($arr === false)
			return fctErrorMSG('Le compte que vous venez de créer n\'existe pas. Contacter un MJ.', $errorUrl);
		
		
		//Instancier le compte
		$compte = $banque->getCompte($arr['compte_compte'], $arr);
		
		
		//Ajouter l'ouverture à l'historique
		$compte->add_bq_hist('','OVRT',0,0);
		
		
		//Rediriger la page
		$tpl->set('PAGE', 'Action_Lieu_Banque');
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/action_redirect.htm',__FILE__,__LINE__);
	}
}

