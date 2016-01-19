<?php
/** Gestion de l'interface d'un guichet automatique: Affichage du panneau de contrôle (solde, retrait, etc)
*
* @package Member_Action
*/
class Member_Action_Lieu_Guichet3
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl1 = '?popup=1&amp;m=Action_Lieu_Guichet';
		$errorUrl2 = '?popup=1&amp;m=Action_Lieu_Guichet2';
	
	
		//Vérifier l'état du perso
		if(!$perso->isAutonome())
			return fctErrorMSG('Vous n\'êtes pas en état d\'effectuer cette action.', $errorUrl1);
			
		//Vérifier si une carte à été sélectionnée
		if (!isset($_POST['carteid']))
			return fctErrorMSG('Aucune carte sélectionnée.', $errorUrl1);
		
		//Valider si le NIP a été saisi
		if (!isset($_POST['nip']))
			return fctErrorMSG('Aucun NIP spécifié.', $errorUrl2, array('carteid' => $_POST['carteid']));
		
		
		
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
			return fctErrorMSG('Cette carte à été supprimée ou désactivée.', $errorUrl1);
			
			
		//Créer le compte bancaire
		$compte = new Member_BanqueCompte($arr);
		
		
		//Créer la carte bancaire
		$carte = $compte->getCarte($_POST['carteid'], $arr);
		
		
		//Valider si la carte est active ou non
		if(!$carte->isValid())
			return fctErrorMSG('Cette carte est actuellement désactivée.', $errorUrl1);
		
		
		
		
		
		//Passer les informations de connexion au template
		$tpl->set('CARD_ID',	$_POST['carteid']	);
		$tpl->set('NIP',		$_POST['nip']		);
		
		
		
		//Valider le NIP et afficher la bonne page selon s'il est valide ou non
		if($carte->getNip() == $_POST['nip'])
		{
			//NIP valide, afficher la page
			$tpl->set('SOLDE',fctCreditFormat($compte->getCash(), true));
			return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Guichet3.htm',__FILE__,__LINE__);
		}
		else
		{
			//NIP invalide, afficher à nouveau le clavier
			$tpl->set('PAGE_WRONGACCESS',true);
			return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Lieu/Guichet2.htm',__FILE__,__LINE__);
		
		}
	}
}

