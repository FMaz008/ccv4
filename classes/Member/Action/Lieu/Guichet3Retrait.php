<?php
/** Gestion de l'interface d'un guichet automatique: Effectuer un retrait
*
* PAGE AJAX
*
* @package Member_Action
*/
class Member_Action_Lieu_Guichet3Retrait{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
	
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			die('00|' . rawurlencode('Votre n\'êtes pas en état d\'effectuer cette action.'));
			
			
		//Vérifier si une carte à été sélectionnée
		if (!isset($_POST['carteid']))
			die('01|' . rawurlencode('Aucune carte sélectionnée.'));
		$tpl->set('CARD_ID',	$_POST['carteid']	);
		
		
		//Valider si le NIP est fourni
		if (!isset($_POST['nip']))
			die('02|' . rawurlencode('Aucun NIP spécifiée.'));
		$tpl->set('NIP',		$_POST['nip']		);
		
		
		
		//Créer la carte + compte
		$query = 'SELECT * '
				. ' FROM `' . DB_PREFIX . 'banque_cartes`'
				. ' LEFT JOIN `' . DB_PREFIX . 'banque_comptes` ON (`compte_banque` = `carte_banque` AND `compte_compte` = `carte_compte`)'
				. ' WHERE `carte_id` = :carteId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':carteId',		$_POST['carteid'],		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		//Valider si la carte existe
		if ($arr === false)
			die('03|' . rawurlencode('Cette carte à été supprimée ou n\'existe pas.'));
		
		
		//Créer le compte bancaire
		$compte = new Member_BanqueCompte($arr);
		
		
		//Créer la carte
		$carte = $compte->getCarte($_POST['carteid'], $arr);
		$tpl->set('COMPTE', $compte->getNoBanque() . '-' . $compte->getNoCompte());
		
		
		//Valider si la carte est active ou non
		if(!$carte->isValid())
			die('03|' . rawurlencode('Cette carte est actuellement désactivée.'));
		
		
		
		
		//Formater le montant pour qu'il soit au bon format
		$_POST['montant'] = str_replace(',','.',$_POST['montant']);
		$montant = round($_POST['montant'],2);
		
		
		//Valider le montant d'argent à retirer
		if(!is_numeric($montant) || $montant<=0)
			die('04|' . rawurlencode('Montant invalide.'));
		
		
		//Valider si le perso possède le montant qu'il retire
		if ($compte->getCash() < $montant && $compte->getCash() != -1)
			die('05|' . rawurlencode('Vous ne pouvez pas retirer plus que le solde de votre compte.'));
		
		
		//Retirer l'argent du compte
		$compte->changeCash('-', $montant);
		$compte->setCash();
		
		//Ajouter l'argent au perso
		$perso->changeCash('+', $montant);
		$perso->setCash();
		
		//Retirer les PA au perso
		$perso->changePa('-', 1);
		$perso->setPa();
		
		
		//Ajouter la transaction à l'historique
		$compte->add_bq_hist('', 'RGUI', $montant, 0);
		
		
		//Confirmer les modifications avec les informations sur les changements
		die($_POST['id'] . '|OK|' . $compte->getCash() . '|' . $perso->getCash() . '|' . $perso->getPa());
	}
}
