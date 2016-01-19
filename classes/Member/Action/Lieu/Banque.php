<?php
/** Gestion de l'interface d'une banque
*
* @package Member_Action
*/
class Member_Action_Lieu_Banque
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		//Cout en PA des actions
		$coutPa = 1;

		if($perso->getPa() <= $coutPa)
			return fctErrorMSG('Vous n\'avez pas assez de PA pour effectuer cette action.', $errorUrl);
		
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
		
		
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
			return fctErrorMSG('Cette banque est actuellement innaccessible ou innexistante (' . $perso->getLieu()->getNomTech() . ').');
		
		//Instancier la banque
		$banque = new Member_Banque($arr);
		$tpl->set('BANQUE', $banque); //Passer l'objet "banque" au template
		
		//###Changer le NIP
		if(isset($_POST['changeNIP']))
		{
			if(!isset($_POST['nip']))
				return fctErrorMSG ('Vous devez entrer un NIP.');

			if(empty($_POST['nip']))
				return fctErrorMSG ('Vous devez entrer un NIP.');

			if(!is_numeric($_POST['nip']))
				return fctErrorMSG ('Le NIP doit être numérique.');

			if($_POST['nip'] < 0)
				return fctErrorMSG ('Le NIP est incorrect.');

			if(!isset($_POST['compte_id']))
				fctBugReport ('Le compte n\'est pas spécifié.', array(), __FILE__, __LINE__);

			//Récupérer le compte
			try
			{
				$compteToChangeNIP = Member_BanqueCompte::getCompteFromId($_POST['compte_id']);
			}
			catch(GameException $e)
			{
				return fctErrorMSG($e->getMessage());
			}
			//Modifier le nip
			$compteToChangeNIP->changeNIP($_POST['nip']);
		}
		
		//###Fermer un compte
		if(isset($_POST['close']))
		{
		
		
			//Valider si le perso possède assez de PA
			if($perso->getPa() < 1)
				return fctErrorMSG('Vous n\'avez pas assez de PA pour effectuer cette action.');
		
		
		
		
		
			//Instancier le compte afin d'y faire des opérations.
			$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'banque_comptes'
					. ' WHERE	compte_id=:compteId'
						. ' AND compte_idperso=:persoId'
					. ' LIMIT 1';
			$prep = $db->prepare($query);
			$prep->bindValue(':compteId',	$_POST['compte_id'],	PDO::PARAM_STR);
			$prep->bindValue(':persoId',	$perso->getId(),		PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arr = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;
			
		
			//Valider si le compte existe
			if($arr === false)
				return fctErrorMSG('Ce compte n\'existe pas (' . $_POST['compte_id'] . ').');
		
		
			//Instancier le compte
			$compte = $banque->getCompte($arr['compte_compte'], $arr);
		
		
			//Valider
			if (!isset($_POST['c_check']))
				return fctErrorMSG('Vous devez cocher la case pour confirmer que vous voulez bien fermer ce compte.','?popup=1&amp;m=Action_Lieu_Banque');
			
			//Effectuer le transfert d'argent (Transférer l'argent du compte vers le perso)
			$perso->changeCash('+', $compte->getCash());
			$perso->setCash();
			
			//Effacer le compte
			$query = 'DELETE FROM ' . DB_PREFIX . 'banque_comptes'
					. ' WHERE	compte_id=:compteId'
						. ' AND compte_idperso=:persoId'
					. ' LIMIT 1';
			$prep = $db->prepare($query);
			$prep->bindValue(':compteId',	$_POST['compte_id'],	PDO::PARAM_STR);
			$prep->bindValue(':persoId',	$perso->getId(),		PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			//Retirer les PA
			$perso->changePa('-', $coutPa);
			$perso->setPa();
			
			//Ajouter la transaction à l'historique
			$compte->add_bq_hist('','FRMT', $compte->getCash(), 0, "FERMÉ");
			Member_He::add('System', $perso->getId(), 'banque', 'Vous fermez un compte en banque.');
			
		}
		
		
		
		
		
		$tpl->set('PA', $perso->getPa());
		$tpl->set('CASH', $perso->getCash());
		
		//Trouver tous les comptes appartenant au perso
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE	compte_banque=:banque'
					. ' AND compte_idperso=:persoId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banque',		$banque->getNoBanque(),	PDO::PARAM_STR);
		$prep->bindValue(':persoId',	$perso->getId(),		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrCompte = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
			
		//Ouvrir un nouveau compte si on le demande ou si aucun compte n'existe
		if (count($arrCompte) == 0 || isset($_POST['newaccount']))
		{
		
			//Afficher le template d'ouverture de compte
			$tpl->set('BANK_ACCOUNT_NAME',	$perso->getNom());
			return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Banque_compte_add.htm',__FILE__,__LINE__);
			
		}
		else
		{
		
			//Faire les opération sur le(s) compte(s) existant(s).
			foreach($arrCompte as &$arr)
				$BANK_ACCOUNTS[] = $banque->getCompte($arr['compte_compte'], $arr);
			
			$tpl->set('BANK_ACCOUNTS',	$BANK_ACCOUNTS);
			
			
			return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Banque.htm',__FILE__,__LINE__);
		}
	}
}

