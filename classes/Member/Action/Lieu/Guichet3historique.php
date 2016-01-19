<?php
/** Gestion de l'interface d'un guichet automatique: Afficher l'historique des transactions
*
* @package Member_Action
*/
class Member_Action_Lieu_Guichet3historique
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl1 = '?popup=1&amp;m=Action_Lieu_Guichet';
		$errorUrl2 = '?popup=1&amp;m=Action_Lieu_Guichet2';
		
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.', $errorUrl1);
			
			
		//Vérifier si une carte à été sélectionnée
		if (!isset($_POST['carteid']))
			return fctErrorMSG('Aucune carte sélectionnée.', $errorUrl1);
		
		
		
		//Valider si le NIP a été saisi
		if (!isset($_POST['nip']))
			return fctErrorMSG('Aucun NIP spécifiée.', $errorUrl2, array('carteid' => $_POST['carteid']));
		
		
		
		//Créer la carte + compte
		$query = 'SELECT *'
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
		{
			fctBugReport( 
				'Cette carte n\'existe pas',
				array(
					'perso' => $perso,
					'query' => $query
				),
				__FILE__,__LINE__,__FUNCTION__,__CLASS__,__METHOD__
			);
		}	
		
		
		//Créer le compte bancaire
		$compte = new Member_BanqueCompte($arr);
		
		
		//Créer la carte
		$carte = $compte->getCarte($_POST['carteid'], $arr);
		
		
		
		//Valider si la carte est active ou non
		if(!$carte->isValid())
			return fctErrorMSG('Cette carte à été désactivée.', $errorUrl1);
		
		
		//Valider le NIP
		if($carte->getNip() != $_POST['nip'])
			return fctErrorMSG('NIP invalide.', $errorUrl2, array('carteid' => $_POST['carteid']));
		
		
		
		//Charger l'historique des transactions
		$compteNo = $compte->getNoBanque() . '-' . $compte->getNoCompte();
		$query = 'SELECT *'
				. ' FROM `' . DB_PREFIX . 'banque_historique`'
				. ' WHERE `compte`=:noCompte;';
		$prep = $db->prepare($query);
		$prep->bindValue(':noCompte',		$compteNo,		PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		//Générer la liste de toutes les transactions et formater les données
		
		$historique = array();
		foreach($arrAll as &$arr)
		{
			$arr['date']	= fctToGameTime($arr['date']);
			$arr['retrait'] = fctCreditFormat($arr['retrait'], true);
			$arr['depot']	= fctCreditFormat($arr['depot'], true);
			$arr['solde']	= fctCreditFormat($arr['solde'], true);
			$historique[] = $arr;
		}
		
		
		//Passer les informations sur l'accès au template
		$tpl->set('COMPTE', $compte->getNoBanque() . '-' . $compte->getNoCompte());
		$tpl->set('CARD_ID',	$_POST['carteid']	);
		$tpl->set('NIP',		$_POST['nip']		);
		$tpl->set('HISTORIQUE',		$historique		);
		
		
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Banque_historique.htm',__FILE__,__LINE__);
	}
}
