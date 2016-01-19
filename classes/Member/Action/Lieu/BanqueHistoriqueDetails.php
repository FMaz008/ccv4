<?php
/** Gestion de l'interface d'un guichet automatique: Afficher les détails sur une transaction
*
* @package Member_Action
*/
class Member_Action_Lieu_BanqueHistoriqueDetails{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
	
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.');
			
		//Vérifier les paramêtres requis
		if(!isset($_POST['compte']))
			return fctErrorMSG('Ce compte est invalide (aucun compte).');
		
		if(!isset($_POST['trsid']))
			return fctErrorMSG('Cette transaction est invalide (aucune transaction).');
			
			
		//Valider le # du compte (TODO: REGEX !!!!)
		if(strlen($_POST['compte'])!=19)
			return fctErrorMSG('Ce compte est invalide (no invalide).');
			
		
		$banque_no = substr($_POST['compte'],0,4);
		$compte_no = substr($_POST['compte'],5,14);
		$tpl->set('COMPTE', $_POST['compte']);
		
		
		//Chercher le compte afin d'y faire des opérations.
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE	compte_banque=:banque'
					. ' AND compte_compte=:compte'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compte',	$compte_no,	PDO::PARAM_STR);
		$prep->bindValue(':banque',	$banque_no,	PDO::PARAM_STR);
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
		
			
			
		//Charger l'historique des transactions
		$fullCompte = $compte->getNoBanque() . '-' . $compte->getNoCompte();
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_historique'
				. ' WHERE compte=:fullCompte'
					. ' AND id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':fullCompte',		$fullCompte,		PDO::PARAM_STR);
		$prep->bindValue(':id',				$_POST['trsid'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		//Valider si la transaction existe
		if ($arr === false)
			return fctErrorMSG('Cette transaction est invalide (transaction innexistante).');
			
		
		//Formater les variables
		$arr['date']	= fctToGameTime($arr['date'], true);
		$arr['solde']	= fctCreditFormat($arr['solde'], true);
		
		
		
		//Afficher le template approprié selon le type de transaction
		switch($arr['code'])
		{
			case 'RETR':
				$arr['montant'] = fctCreditFormat($arr['retrait']);
				$tpl->set('TRANSACTION',		$arr		);
				return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Banque_historique_detailsdirect.htm',__FILE__,__LINE__);
				break;
			case 'DPOT':
				$arr['montant'] = fctCreditFormat($arr['depot']);
				$tpl->set('TRANSACTION',		$arr		);
				return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Banque_historique_detailsdirect.htm',__FILE__,__LINE__);
				break;
			case 'RTRF':
				$arr['montant'] = fctCreditFormat($arr['depot']);
				$tpl->set('TRANSACTION',		$arr		);
				return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Banque_historique_detailstransfert.htm',__FILE__,__LINE__);
				break;
			case 'STRF':
				$arr['montant'] = fctCreditFormat($arr['retrait']);
				$tpl->set('TRANSACTION',		$arr		);
				return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Banque_historique_detailstransfert.htm',__FILE__,__LINE__);
				break;
			default:
				$tpl->set('TRANSACTION',		$arr		);
				return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Banque_historique_detailsnodetails.htm',__FILE__,__LINE__);
				break;
		}
	}
}
