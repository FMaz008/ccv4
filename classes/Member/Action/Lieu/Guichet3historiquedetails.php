<?php
/** Gestion de l'interface d'un guichet automatique: Afficher les détails sur une transaction
*
* @package Member_Action
*/
class Member_Action_Lieu_Guichet3historiquedetails
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl1 = '?popup=1&amp;m=Action_Lieu_Guichet';
		$errorUrl2 = '?popup=1&amp;m=Action_Lieu_Guichet2';
		$errorUrl3 = '?popup=1&amp;m=Action_Lieu_Guichet3historique';
		
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.',$errorUrl1);
			
			
		//Vérifier si une carte à été sélectionnée
		if (!isset($_POST['carteid']))
			return fctErrorMSG('Aucune carte sélectionnée.', $errorUrl1);
		
		
		//Valider si le NIP a été saisi
		if (!isset($_POST['nip']))
			return fctErrorMSG('Aucun NIP spécifiée.', $errorUrl2, array('carteid' => $_POST['carteid']));
		
		
		
		
		
		if (!isset($_POST['trsid']))
			return fctErrorMSG(
						'Transaction invalide (1).',$errorUrl3,
						array('carteid' => $_POST['carteid'], 'nip' => $_POST['nip'])
					);
			
		
		
		
		
		
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
		
		//Valider si la carte existe dans la base de données
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
		$noCompte = $compte->getNoBanque() . '-' . $compte->getNoCompte();
		$query = 'SELECT * '
				. ' FROM `' . DB_PREFIX . 'banque_historique`'
				. ' WHERE `compte`=:noCompte'
					. ' AND `id`=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':noCompte',	$noCompte,			PDO::PARAM_STR);
		$prep->bindValue(':id',			$_POST['trsid'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		//Valider si la transaction existe dans la base de données
		if ($arr === false)
			return fctErrorMSG(
						'Transaction invalide (2).', $errorUrl3,
						array('carteid' => $_POST['carteid'], 'nip' => $_POST['nip'])
					);
					
		
		//Récolter et formater les informations relative à la transaction
		$arr['date'] = fctToGameTime($arr['date'], false, true);
		$arr['solde']	= fctCreditFormat($arr['solde'], true);
		
		
		
		//Passer les informations au template
		$tpl->set('CARD_ID',	$_POST['carteid']	);
		$tpl->set('NIP',		$_POST['nip']		);
		$tpl->set('COMPTE', $compte->getNoBanque() . '-' . $compte->getNoCompte());
		
		
		
		//Afficher la page adéquate selon le type de transaction
		switch($arr['code']){
			case "RETR":
				$arr['montant'] = fctCreditFormat($arr['retrait']);
				$tpl->set('TRANSACTION',		$arr		);
				return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Banque_historique_detailsdirect.htm',__FILE__,__LINE__);
				break;
			case "DPOT":
				$arr['montant'] = fctCreditFormat($arr['depot']);
				$tpl->set('TRANSACTION',		$arr		);
				return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Banque_historique_detailsdirect.htm',__FILE__,__LINE__);
				break;
			case "RTRF":
				$arr['montant'] = fctCreditFormat($arr['depot']);
				$tpl->set('TRANSACTION',		$arr		);
				return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Banque_historique_detailstransfert.htm',__FILE__,__LINE__);
				break;
			case "STRF":
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
