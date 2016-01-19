<?php
/** AJAX: Gestion de l'ouverture d'un site internet
*
* @package Member_Action
* @subpackage Ajax
*/
class Member_Action_Item_Siteadd
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante	//BUT: Démarrer un template propre à cette page
		
		$actionPa = 3;
		$cout_ouverture = 7500;
		
		if($perso->getPa() <= $actionPa)
			return fctErrorMsg('Vous n\'avez pas assez de PA pour effectuer cette action.');
		
		if(!isset($_POST['url']))
			return fctErrorMsg('Paramètre requis manquant.');
		
		//Valider si le joueur à accès à Internet
		if(Member_Action_Item_Navigateur::checkAccess($perso)===false)
			return fctErrorMSG('Vous n\'avez pas accès à Internet.');
			
			
		
		//Vérifier l'état du perso
		if(!$perso->isConscient())
			die('01|' . 'Votre n\'êtes pas en état d\'effectuer cette action.');
		
		if($perso->getPa() < $actionPa)
			die('02|' . 'Vous n\'avez pas assez de PA pour effectuer cette action.');
		
		if(!preg_match('/^[A-Za-z0-9\.-_]+$/', $_POST['url'], $matches))
			die('03|' . 'L\'URL dU site est invalide.');
		
		if(!preg_match('/^([0-9]{4})-([0-9]{4}-[0-9]{4}-[0-9]{4})-([0-9]+)$/', $_POST['no'], $matches))
			die('04|' . 'Le # de la carte est invalide.');
		
		$carte_banque = $matches[1];
		$carte_compte = $matches[2];
		$carte_id = $matches[3];
		
		$query = 'SELECT *'
			. ' FROM ' . DB_PREFIX . 'banque_cartes'
			. ' WHERE 	carte_banque=:carteBanque'
				. ' AND carte_compte=:carteCompte'
				. ' AND carte_id =:carteId'
			. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':carteBanque',	$carte_banque,		PDO::PARAM_STR);
		$prep->bindValue(':carteCompte',	$carte_compte,		PDO::PARAM_STR);
		$prep->bindValue(':carteId',		$carte_id,			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$carte = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if ($carte === false)
			die('05|' . 'Cette carte est innexistante.');
		
		
		if($carte['carte_valid']==0)
			die('06|' . 'Cette carte a été désactivée.');
		
		if($carte['carte_nip'] != $_POST['nip'])
			die('07|' . 'NIP erroné.');
		
		
		//Valider le montant du compte associé à la carte
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE	compte_banque=:carteBanque'
					. ' AND compte_compte=:carteCompte;';
		$prep = $db->prepare($query);
		$prep->bindValue(':carteBanque',	$carte_banque,		PDO::PARAM_STR);
		$prep->bindValue(':carteCompte',	$carte_compte,		PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
			
		if ($arr === false)
			die('08|' . 'Le compte associé à la carte a été fermé.');
		
		$compte = new Member_BanqueCompte($arr);
		
		if ($compte->getCash() < $cout_ouverture)
			die('09|' . 'Compte sans fond.');
		
		
		//Vérifier si l'URL existe déjà
		$query = 'SELECT id'
				. ' FROM ' . DB_PREFIX . 'sitesweb'
				. ' WHERE url=:url;';
		$prep = $db->prepare($query);
		$prep->bindValue(':url',	$_POST['url'],		PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		
		if ($arr !== false)
			die('10|' . 'Cette URL existe déjà, veuillez en choisir une autre.');
			
			
			
			
		//Tout est ok, Créer le site !!!!! 
		
		$perso->changePa('-', $actionPa);
		$perso->setPa();
		
		$compte->changeCash('-', $cout_ouverture);
		$compte->setCash();
		
		$compte->add_bq_hist('', 'WWWA', $cout_ouverture);
		
		$query = 'INSERT INTO ' . DB_PREFIX . 'sitesweb'
				. ' (`url`, `titre`, `acces`)'
				. ' VALUES'
				. ' (:url, :titre, "pub");';
		$prep = $db->prepare($query);
		$prep->bindValue(':url',	$_POST['url'],		PDO::PARAM_STR);
		$prep->bindValue(':titre',	$_POST['titre'],	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		$siteid = $db->lastInsertId();
		
		
		//Créer l'accès admin au site
		
		$query = 'INSERT INTO ' . DB_PREFIX . 'sitesweb_acces'
				. ' (`site_id`, `user`, `pass`, `accede`, `poste`, `modifier`, `admin`)'
				. ' VALUES'
				. ' (:siteId, :user, :pass, "1", "1", "1", "1");';
		$prep = $db->prepare($query);
		$prep->bindValue(':siteId',		$siteid,			PDO::PARAM_INT);
		$prep->bindValue(':user',		$_POST['user'],		PDO::PARAM_STR);
		$prep->bindValue(':pass',		$_POST['pass'],		PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		
		die('OK|' . $perso->getPa() . '|' . $_POST['url']); //Tout est OK
	}
}

