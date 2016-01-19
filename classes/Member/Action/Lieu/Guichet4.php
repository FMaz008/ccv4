<?php
/** Gestion de l'interface d'un guichet automatique: Effectuer un retrait
*
* @package Member_Action
*/
class Member_Action_Lieu_Guichet4
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl1 = '?popup=1&amp;m=Action_Lieu_Guichet';
		$errorUrl2 = '?popup=1&amp;m=Action_Lieu_Guichet2';
		$errorUrl3 = '?popup=1&amp;m=Action_Lieu_Guichet3';
		
		
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.',$errorUrl1);
		
		
		//Vérifier si une carte à été sélectionnée
		if (!isset($_POST['carteid']) || !is_numeric($_POST['carteid']))
			return fctErrorMSG('Aucune carte sélectionnée.', $errorUrl1);
		
		
		//Vérifier si un NIP a été saisie
		if (!isset($_POST['nip']))
			return fctErrorMSG('Aucun NIP spécifiée.', $errorUrl, array('carteid' => $_POST['carteid']));
		
		
		
		
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
		
		
		//Créer le compte de banque
		$compte = new Member_BanqueCompte($arr);
		
		
		//Créer la carte bancaire
		$carte = $compte->getCarte($_POST['carteid'], $arr);
		
		
		//Valider si la carte est active ou non
		if(!$carte->isValid())
			return fctErrorMSG('Cette carte à été désactivée.', $errorUrl1);		
		
		
		
		//Placer les informations sur l'accès dans le template
		$tpl->set('CARD_ID',	$_POST['carteid']	);
		$tpl->set('NIP',		$_POST['nip']		);
		$tpl->set('COMPTE', $compte->getNoBanque() . '-' . $compte->getNoCompte());
		
		
		
		
		
		//Valider le montant d'argent à retirer
		$montant = round($_POST['retrait'],2);
		if(!is_numeric($montant) || $montant<=0)
			return fctErrorMSG(
						'Montant invalide.',
						'?m=Action_Lieu_Guichet3',
						array('carteid' => $_POST['carteid'], 'nip' => $_POST['nip'])
					);
		
		
		//Valider si le montant
		if ($compte->getCash() < $montant && $compte->getCash() != -1)
			return fctErrorMSG(
						'Tentative de retrait supérieur au montant maximal autorisé.',
						$errorUrl3,
						array('carteid' => $_POST['carteid'], 'nip' => $_POST['nip'])
					);
		
		
		
		//Retirer l'argent du compte
		$compte->changeCash('-', $montant);
		$compte->setCash();
		
		
		//Ajouter l'argent au perso
		$perso->changeCash('+', $montant);
		$perso->setCash();
		
		//Retirer les PA au perso
		$perso->changePa('-', 1);
		$perso->setPa();
		
		
		//Ajouter le message à l'historique bancaire
		Member_BanqueCompte::addHist(
			$noCompte,						//Du compte
			'',								//Vers le compte
			'RGUI',							//Type de transaction
			$montant,						//Montant retrait
			0,								//Montant dépot
			($compte->getCash()-$montant)	//Solde
		);
		
		
		
		//Copier le message dans les HE
		Member_He::add('', $perso->getId(), 'parler', "Vous effectuez un retrait de " . fctCreditFormat($montant, true) . " au guichet automatique.");
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}

