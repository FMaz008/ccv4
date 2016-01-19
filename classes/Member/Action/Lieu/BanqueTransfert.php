<?php
/** AJAX: Effectuer un retrait bancaire
*
* @package Member_Action
* @subpackage Ajax
* @todo Créer des regexp pour valider les # de comptes
*/
class Member_Action_Lieu_BanqueTransfert
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
		
		
		if(!isset($_POST['compte']) || !isset($_POST['id']))
			return fctErrorMSG('Veuillez passer par le formulaire.');
		
		
		$occ = preg_match('/([0-9]{4})-([0-9]{4})-([0-9]{4})-([0-9]{4})/', $_POST['compte']);
		if($occ==0)
			return fctErrorMSG('Le # de compte doit être au format XXXX-XXXX-XXXX-XXXX.', $errorUrl);
		
		
		
		//Instancier la banque
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque'
				. ' WHERE banque_lieu=:nomTech'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':nomTech',	$perso->getLieu()->getNomTech(),	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
			
		if ($arr === false)
			die('02|Cette banque n\'existe pas (' . $perso->getLieu()->getNomTech() . ').');


		$banque = new Member_Banque($arr);

		
		
		//Rechercher le compte
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE	compte_id=:compteId'
					. ' AND compte_idperso=:persoId;'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compteId',	$_POST['id'],		PDO::PARAM_INT);
		$prep->bindValue(':persoId',	$perso->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
			
		//Valider si le compte existe
		if ($arr === false)
			die('03|Ce compte n\'existe pas (' . $_POST['id'] . ').');
		
		
		//Instancier le compte
		$compte = $banque->getCompte($arr['compte_compte'], $arr);
		
		
		
		//Rechercher le compte distant (celui vers lequel faire le transfert)
		$cptno = explode('-',$_POST['compte']);
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE	compte_banque=:banque'
					. ' AND compte_compte=:compte'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banque',	$cptno[0],		PDO::PARAM_INT);
		$prep->bindValue(':compte',	$cptno[1] . '-' . $cptno[2] . '-' . $cptno[3],		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		
		//Valider si le compte a été trouvé
		if ($arr === false)
			die('03|Ce compte n\'existe pas (' . $_POST['compte'] . ').');
		
		
		//Instancier le compte distant (celui vers lequel faire le transfert)
		$compte2 = $banque->getCompte($arr['compte_compte'], $arr);
		
		
		//Valider si le montant est possible
		$_POST['montant'] = str_replace(',','.',$_POST['montant']);
		if ($_POST['montant']<=0 || ($_POST['montant']>$compte->getCash() && $compte->getCash() != -1))
			die('04|Vous ne pouvez pas retirer plus que vous avez ou un montant vide.');
		
		
		
		//Effectuer le transfert d'argent
		$compte->changeCash('-', $_POST['montant']);
		$compte->setCash();
		
		$compte2->changeCash('+', $_POST['montant']);
		$compte2->setCash();
		
		
		//Retirer les PA
		$perso->changePa('-', $coutPa);
		$perso->setPa();
		
		
		//Ajouter la transaction à l'historique
		$compte->add_bq_hist($compte2->getNoBanque() . '-' . $compte2->getNoCompte(), 'STRF', $_POST['montant'], 0);
		$compte2->add_bq_hist($compte->getNoBanque() . '-' . $compte->getNoCompte(), 'RTRF', 0 ,$_POST['montant']);
		
		$compteCash = ($compte->getCash() == -1)? 'illimité' : $compte->getCash();
		//Confirmer les modifications avec les informations sur les changements
		die($_POST['id'] . '|OK|' . $compteCash . '|' . $perso->getCash() . '|' . $perso->getPa());
	}
}
