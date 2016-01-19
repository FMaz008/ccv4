<?php
/** Affichage de la page d'instruction pour la création d'un personnage.
 *
 * @package Member
 * @subpackage Inscription
 */
class Member_CreerPerso3
{
	public static function generatePage(&$tpl, &$session, &$account)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?m=CreerPerso2';
		
		
		//Si les magicQuote sont activer, les retirer.
		fctStripMagicQuote($_POST);
		
		
		//Déterminer la quantité de perso que ce compte à l'autorisation de créer
		$query = 'SELECT auth_creation_perso'
				. ' FROM ' . DB_PREFIX . 'account'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',		$account->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if ($arr['auth_creation_perso']==0)
			return fctErrorMSG('Vous n\'avez pas l\'autorisation de créer un personnage. (cheat)', '?m=News', null, false);
		
		
		
		
		
		//Validation du NOM
		if(!isset($_POST['nom']) || empty($_POST['nom']))
			return fctErrorMSG('Nom de personnage manquant.', $errorUrl, $_POST, false);
		
		if(strlen($_POST['nom'])>25 || strlen($_POST['nom'])<4)
			return fctErrorMSG('Le nom du personnage à une taille invalide.', $errorUrl, $_POST, false);
		
		preg_match('/^[-\' çéèôêîA-Za-z]+$/', $_POST['nom'], $matches);
		if (count($matches)==0)
			return fctErrorMSG('Le nom du personnage comporte des caractères invalide.', $errorUrl, $_POST, false);
		
		$query = 'SELECT id'
				. ' FROM ' . DB_PREFIX . 'perso'
				. ' WHERE nom=:nom'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':nom',	$_POST['nom'],	PDO::PARAM_STR);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr!==false)
			return fctErrorMSG('Ce nom est déjà utilisé.', $errorUrl, $_POST, false);
		
		
		
		//Validation de l'ETHNIE
		if(!isset($_POST['ethnie']) || empty($_POST['ethnie']))	
			return fctErrorMSG('Ethnie du personnage manquant.', $errorUrl, $_POST, false);

		
		
		//Validation de l'AGE
		if(!isset($_POST['age']) || empty($_POST['age']))	
			return fctErrorMSG('Âge du personnage manquant.', $errorUrl, $_POST, false);
		
		preg_match('/^[0-9]{1,2}$/', $_POST['age'], $matches);
		if (count($matches)==0)
			return fctErrorMSG('L\'âge du personnage comporte des caractères invalide.', $errorUrl, $_POST, false);
		
		
		
		//Validation du POIDS
		if(!isset($_POST['poids']) || empty($_POST['poids']))	
			return fctErrorMSG('Poids du personnage manquant.', $errorUrl, $_POST, false);
		
		preg_match('/^[0-9]{1,3}$/', $_POST['poids'], $matches);
		if (count($matches)==0)
			return fctErrorMSG('Le poids du personnage comporte des caractères invalide.', $errorUrl, $_POST, false);
		
		
		
		//Validation de la TAILLE
		if(!isset($_POST['taillem']) || empty($_POST['taillem']))	
			return fctErrorMSG('Taille du personnage manquante (1).', $errorUrl, $_POST, false);
			
		if(!isset($_POST['taillecm']) || empty($_POST['taillecm']))	
			return fctErrorMSG('Taille du personnage manquante (2).', $errorUrl, $_POST, false);
		
		$taille = $_POST['taillem'] . 'm' . $_POST['taillecm'];
		preg_match('/^[12][m][0-9][05]$/', $taille, $matches);
		if (count($matches)==0)
			return fctErrorMSG('La taille du personnage comporte des caractères invalide.', $errorUrl, $_POST, false);
		
		
		
		//Validation des YEUX
		if(!isset($_POST['yeux']) || empty($_POST['yeux']))	
			return fctErrorMSG('Description des yeux du personnage manquante.', $errorUrl, $_POST, false);
		
		if(strlen($_POST['yeux'])>100)
			return fctErrorMSG('add - Description des yeux du personnage à une taille invalide (' . strlen($_POST['yeux']) . ').', $errorUrl, $_POST, false);
		
		
		//Validation des CHEVEUX
		if(!isset($_POST['cheveux']) || empty($_POST['cheveux']))	
			return fctErrorMSG('Description des cheveux du personnage manquante.', $errorUrl, $_POST, false);
		
		if(strlen($_POST['cheveux'])>100)
			return fctErrorMSG('add - Description des cheveux du personnage à une taille invalide (' . strlen($_POST['cheveux']) . ').', $errorUrl, $_POST, false);
		
		
		
		//Effectuer la liste de toutes les statistiques
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'stat;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__,__LINE__);
		$arrStat = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		
		
		//Validation des STATS
		$totalStat = 0;
		foreach($arrStat as &$stat)
		{
			$fieldName = 'stat_' . $stat['id'];
			
			if(!isset($_POST[$fieldName]) || !is_numeric($_POST[$fieldName]) || $_POST[$fieldName] < -75 || $_POST[$fieldName] > 75)	
				return fctErrorMSG('Données sur les stats invalides.', $errorUrl, $_POST, false);
		
			$totalStat += $_POST[$fieldName];
		}
		if($totalStat != 0)
			return fctErrorMSG('Les statistiques doivent avoir une somme de zéro.', $errorUrl, $_POST, false);
		
		
		//Effectuer la liste de toutes les compétences
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'competence'
				. ' WHERE inscription="1";';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__,__LINE__);
		$arrComp = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		
		
		//Validation des COMPS
		$totalComp = 0;
		foreach($arrComp as &$comp)
		{
			$fieldName = 'comp_' . $comp['id'];
			
			if(!isset($_POST[$fieldName]) || !is_numeric($_POST[$fieldName]) || $_POST[$fieldName] < 0 || $_POST[$fieldName] > INSCRIPTION_MAX_COMP)	
				return fctErrorMSG('Données sur les compétences invalides.', $errorUrl, $_POST, false);
		
			$totalComp += $_POST[$fieldName];
		}
		if($totalComp != INSCRIPTION_NBR_COMP)
			return fctErrorMSG('Les compétences doivent avoir une somme de ' . INSCRIPTION_NBR_COMP . '.', $errorUrl, $_POST, false);
		
		
		
		
		
		//Validation SIGNES
		if(!isset($_POST['arrCaract']))
			return fctErrorMSG('Sélection des caractéristiques du personnage manquante.', $errorUrl, $_POST, false);
			
		if(count($_POST['arrCaract'])<5)
			return fctErrorMSG('Vous n\'avez sélectionné que ' . count($_POST['arrCaract']) . ' caractéristique(s).', $errorUrl, $_POST, false);
		
		
		
		//Validation Background + Description
		if(!isset($_POST['description']) || empty($_POST['description']))	
			return fctErrorMSG('Description du personnage manquante.', $errorUrl, $_POST, false);
			
		if(!isset($_POST['background']) || empty($_POST['background']))	
			return fctErrorMSG('Historique du personnage manquante.', $errorUrl, $_POST, false);
		
		
		
	
		
		
		
		
		
		//Protéger tout les champs de premier niveau
		foreach($_POST as &$elem)
			if(!is_array($elem))
				$elem = fctScriptProtect($elem);
		
		
		
		
		
		//Créer le personnage
		if(isset($_POST['go']))
			$Pstatus = '0';

		if(isset($_POST['save']))
			$Pstatus = 'mod';


		try
		{
			$db->beginTransaction();

			
			$query = 'INSERT INTO ' . DB_PREFIX . 'perso'
					. ' ('
					. ' `userId`, `nom`, `sexe`, `age`, `taille`, `yeux`,'
					. ' `ethnie`, `cheveux`, `poids`,'
					. ' `lng1`, `lng1_lvl`, `lng2`, `lng2_lvl`,'
					. ' `lieu`, `description`, `background`, `inscription_valide`'
					. ' )'
					. ' VALUES'
					. ' ('
					. ' :userId,  :nom,  :sexe,  :age,  :taille,  :yeux,'
					. ' :ethnie,  :cheveux,  :poids,'
					. ' "en",	:lng1_lvl,  :lng2,  :lng2_lvl,'
					. ' :lieu,  :description,  :background,  :saveType'
					. ' );';
			$prep = $db->prepare($query);
			$saveType = $_POST['SaveType']=='go' ? '0' : 'mod';
			
			
			
			$prep->bindValue(':userId', 	$account->getId(),	PDO::PARAM_INT);
			$prep->bindValue(':nom', 		$_POST['nom'],		PDO::PARAM_STR);
			$prep->bindValue(':sexe', 		$_POST['sexe'],		PDO::PARAM_STR);
			$prep->bindValue(':age', 		$_POST['age'],		PDO::PARAM_INT);
			$prep->bindValue(':taille', 	$taille,			PDO::PARAM_STR);
			$prep->bindValue(':yeux', 		$_POST['yeux'],		PDO::PARAM_STR);
			$prep->bindValue(':ethnie', 	$_POST['ethnie'],	PDO::PARAM_STR);
			$prep->bindValue(':cheveux', 	$_POST['cheveux'],	PDO::PARAM_STR);
			$prep->bindValue(':poids',	 	$_POST['poids'],	PDO::PARAM_STR);
			$prep->bindValue(':lng1_lvl',	$_POST['lng1_lvl'],	PDO::PARAM_STR);
			$prep->bindValue(':lng2',		$_POST['lng2'],		PDO::PARAM_STR);
			$prep->bindValue(':lng2_lvl',	$_POST['lng2_lvl'],	PDO::PARAM_STR);
			$prep->bindValue(':lieu',		INNACTIVITE_VOLUNTARY_LOCATION,	PDO::PARAM_STR);
			$prep->bindValue(':description',$_POST['description'],	PDO::PARAM_STR);
			$prep->bindValue(':background',	$_POST['background'],	PDO::PARAM_STR);
			$prep->bindValue(':saveType',	$saveType,			PDO::PARAM_STR);
			$prep->execute($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			$persoId = $db->lastInsertId();
			
			
			
			
			//Insérer les stats
			$query = 'INSERT INTO ' . DB_PREFIX . 'perso_stat'
					. ' (`persoid`, `statid`, `xp`)'
					. ' VALUES'
					. ' (:persoId,  :statId,  :xp);';
			$prep = $db->prepare($query);
			
			foreach($arrStat as &$stat)
			{
				$statXp = $_POST['stat_' . $stat['id']];
				$prep->bindValue(':persoId',	$persoId, 	PDO::PARAM_INT);
				$prep->bindValue(':statId',		$stat['id'],PDO::PARAM_INT);
				$prep->bindValue(':xp',			$statXp, 	PDO::PARAM_INT);
				$prep->execute($db, __FILE__,__LINE__);
			}
			$prep->closeCursor();
			$prep = NULL;


			
			
			//Insérer les compétences
			$query = 'INSERT INTO ' . DB_PREFIX . 'perso_competence'
					. ' (`persoid`, `compid`, `xp`)'
					. ' VALUES'
					. ' (:persoId,  :compId,  :xp);';
			$prep = $db->prepare($query);
			
			foreach($arrComp as &$comp)
			{
				$fn = 'comp_' . $comp['id'];
				if(isset($_POST[$fn]) && is_numeric($_POST[$fn]) && $_POST[$fn] > 0)
				{
					$compXp = Member_Perso::convCompLevelToXp($_POST[$fn]);

					$prep->bindValue(':persoId',	$persoId, 	PDO::PARAM_INT);
					$prep->bindValue(':compId',		$comp['id'],PDO::PARAM_INT);
					$prep->bindValue(':xp',			$compXp, 	PDO::PARAM_INT);
					$prep->execute($db, __FILE__,__LINE__);
				}
			}
			$prep->closeCursor();
			$prep = NULL;
			


			
			//Ajouter les informations sur les Signes
			$query =  'INSERT INTO ' . DB_PREFIX . 'perso_caract'
					. ' (`persoid`, `caractid`, `desc`)'
					. ' VALUES'
					. ' (:persoId,  :caractId,  :desc);';
			$prep = $db->prepare($query);
			
			foreach($_POST['arrCaract'] as &$caract)
			{
				$desc = $_POST['caractDesc_' . $caract];
				$prep->bindValue(':persoId',	$persoId, 	PDO::PARAM_INT);
				$prep->bindValue(':caractId',	$caract,	PDO::PARAM_INT);
				$prep->bindValue(':desc',		$desc, 		PDO::PARAM_STR);
				$prep->execute($db, __FILE__,__LINE__);
			}
			$prep->closeCursor();
			$prep = NULL;
			
			
			//Baisser le nombre de création de perso de 1
			$query = 'UPDATE ' . DB_PREFIX . 'account'
					. ' SET auth_creation_perso=auth_creation_perso-1'
					. ' WHERE id=:userId'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':userId',		$account->getId(), 		PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			
			//Sauvegarder la liste des personnages du compte
			$perso_list = array();
			$perso_list = $session->getVar('persoList');
			$perso_list[]= array('id'=>$persoId, 'nom'=>$_POST['nom']);
			$session->setVar('persoList', $perso_list);
			
			
			$db->commit();
		}
		catch(exception $e)
		{
			$db->rollback();
			var_dump($e);
			throw $e;
		}
		
		//Retourner le template complété/rempli
		$tpl->set('PAGE', 'News');
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/redirect.htm',__FILE__,__LINE__);
		
	}
	
	
	/** Converti un niveau de bonus fictif en XP.
	 * Permet au joueur de déterminer son niveau d'XP de départ à l'inscription
	 */
	/*
	public static function convStatPseudoLevelToXp($lvl)
	{
		$lvl+=3; //-3=0, +3=6
		if($lvl<0)
			$lvl=0;
		if($lvl>6)
			$lvl=6;
		
		switch($lvl)
		{
			case 0:	return -100;	break;
			case 1: return -65;		break;
			case 2: return -30;		break;
			case 3: return 0;		break;
			case 4: return 30;		break;
			case 5: return 65;		break;
			case 6: return 100;		break;
		}
	}
	*/
}

