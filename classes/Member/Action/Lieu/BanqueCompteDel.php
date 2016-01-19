<?php
/** Gestion des cartes associés à un compte
*
* @package Member_Action
*/
class Member_Action_Lieu_BanqueCarteDel
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
	
		$coutPa = 1;
		if($perso->getPa() <= $coutPa)
			return fctErrorMSG('Vous n\'avez pas assez de PA pour effectuer cette action.', $errorUrl);
		
		
		//Vérifier les paramêtres requis
		if(!isset($_POST['compte']))
			return fctErrorMSG('Ce compte est invalide (aucun compte).');
		
		
		//Valider si la case de confirmation à bien été cochée
		if (!isset($_POST['c_check']))
			return fctErrorMSG('Vous devez cocher la case pour confirmer que vous voulez bien fermer ce compte.','?popup=1&amp;m=Action_Lieu_Banque');
		
		
		//Valider le # du compte (TODO: REGEX !!!!)
		if(strlen($_POST['compte'])!=19)
			return fctErrorMSG('Ce compte est invalide (no invalide).');
		
		
		$banque_no = substr($_POST['compte'],0,4);
		$compte_no = substr($_POST['compte'],5,14);
		$tpl->set('COMPTE', $_POST['compte']);
		
		
		//Rechercher le compte afin d'y faire des opérations.
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE	compte_banque=:banque'
					. ' AND compte_compte:compte'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compte',		$compte_no,	PDO::PARAM_STR);
		$prep->bindValue(':banque',		$banque_no,	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		//Valider si le compte existe
		if($arr === false)
			return fctErrorMSG('Ce compte n\'existe pas.');
		
		//Instancier le compte
		$compte = new Member_BanqueCompte($arr);
				
				
		//Vérifier si le compte appartiend bien au perso
		if ($compte->getIdPerso() != $perso->getId())
			return fctErrorMSG('Ce compte ne vous appartiend pas.');
		
		

		
		//Effectuer le transfert d'argent (Transférer l'argent du compte vers le perso)
		$perso->changeCash('+', $compte->getCash());
		$perso->setCash();
		
		
		
		//Effacer le compte
		$query = 'DELETE FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE	compte_id=:compteId'
					. ' AND compte_idperso=:persoId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compteId',	$_POST['id'],		PDO::PARAM_INT);
		$prep->bindValue(':persoId',	$perso->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;

		
		//Retirer les PA
		$perso->changePa('-', $coutPa);
		$perso->setPa();
		
		
		
		
		
		//Ajouter l'ouverture à l'historique
		$compte->add_bq_hist('','FRMT', $compte->getCash(), 0, "FERMÉ");
		
		
		//Afficher la page
		$tpl->set('PAGE', 'Action_Lieu_Banque');
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/action_redirect.htm',__FILE__,__LINE__);
	}
}

