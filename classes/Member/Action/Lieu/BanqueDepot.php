<?php
/** AJAX: Effectuer un retrait bancaire
*
* @package Member_Action
* @subpackage Ajax
*/
class Member_Action_Lieu_BanqueDepot
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		$coutPa = 1;
		
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			die('00|Votre n\'êtes pas en état d\'effectuer cette action.');
		
		
		//Vérifier les PA du perso
		if($perso->getPa() <= $coutPa)
			die('01|Vous n\'avez pas assez de PA pour effectuer cette action.');
		
		
		//Rechercher la banque
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque'
				. ' WHERE banque_lieu=:nomTech'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':nomTech',	$perso->getLieu()->getNomTech(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		
		
		//Valider si la banque existe
		if ($arr === false)
			die('02|Cette banque n\'existe pas (' . $perso->getLieu()->getNomTech() . ').');
		
		
		//Intancier le compte bancaire
		$banque = new Member_Banque($arr);
		
		
		//Rechercher le compte
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE	compte_id=:compteId'
					. ' AND compte_idperso=:persoId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compteId',		$_POST['id'],		PDO::PARAM_INT);
		$prep->bindValue(':persoId',		$perso->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		//Valider le compte
		if ($arr === false)
			die('03|Ce compte n\'existe pas (' . $_POST['id'] . ').');
			
		
		//Instancier le compte
		$compte = $banque->getCompte($arr['compte_compte'], $arr);
		
		
		//Valider si le montant est possible
		$montant = str_replace(',','.',$_POST['montant']);
		if ($montant<=0 || $montant > $perso->getCash())
			die('04|Vous ne pouvez pas déposer plus que vous avez ou un montant vide.');
		
		
		//Effectuer le transfert d'argent
		$perso->changeCash('-', $montant);
		$perso->setCash();
		
		$compte->changeCash('+', $montant);
		$compte->setCash();
		
		//Retirer les PA
		$perso->changePa('-', $coutPa);
		$perso->setPa();
		
		//Ajouter la transaction à l'historique
		$compte->add_bq_hist('', 'DPOT', 0, $montant);
		
		$compteCash = ($compte->getCash() == -1)? 'illimité' : $compte->getCash();
		//Confirmer les modifications avec les informations sur les changements
		die($_POST['id'] . '|OK|' . $compteCash . '|' . $perso->getCash() . '|' . $perso->getPa());
	}
}

