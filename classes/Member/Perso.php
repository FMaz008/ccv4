<?php
/** 
 * Gestion des personnages.
 * 
 * Cette classe s'applique autant pour le personnage actuellement
 * joué que pour les personnages divers (dans un lieu par exemple)
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.1
 * @package Member
 */

class Member_Perso
{
	/**#@+
	 * @access private
	 * @var int
	 */
	private $id;
	private $userId;
	private $age;
	private $pa;
	private $paMax;
	private $pv;
	private $pvMax;
	private $lastSavedPv; //Pour la détection&log de la mort ( voir setPV )
	private $pn;
	private $pnMax;
	private $pr;
	private $prMax;
	private $heQte;
	
	/*
	 * NULL ou inv_id
	 */
	private $menotte;
	/**#@-*/
	
	/**#@+
	 * @access private
	 * @var array
	 */
	/*
	 * Statistiques
	 */
	private $stat;
	
	/*
	 * Compétences
	 */
	private $comp;
	
	/*
	 * Items en inventaire
	 * @see Member_Item
	 */
	private $inventaire;
	/**#@-*/
	
	/**#@+
	 * @access private
	 * @var string
	 */
	private $nom;
	private $type;
	private $ethnie;
	private $yeux;
	private $cheveux;
	private $description;
	private $background;
	private $noteMj;
	private $currentAction;
	private $lng1;
	private $lng1_lvl;
	private $lng2;
	private $lng2_lvl;
	private $visaPerm;
	
	/*
	 * URL vers l'image
	 */
	private $avatar;
	
	/*
	 * 'm' = Homme
	 * 'f' = Femme
	 */
	private $sexe;
	private $taille;
	
	/*
	 * Riposte automatique
	 * 'rien' = Aucune riposte
	 * 'riposte' = Riposte
	 * 'fuir' = Essayer de fuir
	 */
	private $reaction;
	
	/*
	 * Statut de l'inscription
	 * '0' = Non-validé
	 * '1' = Validé
	 * 'mod' = Doit être modifié
	 */
	private $inscrValide;
	/**#@-*/
	
	
	/**
	 * @access private
	 * @var float
	 * @todo Stocker un INT, getCash/100, setCash*100
	 */
	private $cash;
	
	
	/**#@+
	 * @access private
	 * @var object
	 */
	/*
	 * @see Member_Lieu
	 */
	private $lieu;
	
	/*
	 * @see Member_ItemArme
	 */
	private $arme;	//Arme équipée du perso
	/**#@-*/
	
	
	
	/**#@+
	 * @access private
	 * @var bool
	 */
	 
	/**
	 * Déjà soigné ?
	 */
	private $soin;
	
	/**
	 * Perso bloqué ?
	 */
	private $bloque;
	
	/**
	 * En cas d'attaque, esquive ?
	 */
	private $esquive;
	/**#@-*/
			
	
	
	
	
	
	
	
	
	
	
	/** 
	 * Constructeur pour la classe des personnages
	 *
	 * Exemple d'utilisation - Charger un joueur selon son ID
	 * <code>
	 * $query = 'SELECT * FROM ' . DB_PREFIX . 'perso WHERE id=:persoId;';
	 * 	$prep = $db->prepare($query);
	 *	$prep->bindValue(':persoId',	801,	PDO::PARAM_INT);
	 *	$prep->executePlus($db, __FILE__, __LINE__);
	 *	$arr = $prep->fetch();
	 *	$prep->closeCursor();
	 *	$prep = NULL;
	 *
	 * $perso = new Perso($arr);
	 * </code>
	 *
	 * Exemple d'utilisation - Charger un joueur selon son ID (incluant la prise en charge du lieu et son inventaire)
	 * <code>
	 * $query = 'SELECT * FROM ' . DB_PREFIX . 'perso WHERE id=:persoId;';
	 * 	$prep = $db->prepare($query);
	 *	$prep->bindValue(':persoId',	801,	PDO::PARAM_INT);
	 *	$prep->executePlus($db, __FILE__, __LINE__);
	 *	$arr = $prep->fetch();
	 *	$prep->closeCursor();
	 *	$prep = NULL;
	 *
	 * $perso = new Perso($arr, true, true);
	 * </code>
	 *
	 * Exemple d'utilisation - Charger la liste des joueurs d'un lieu
	 * <code>
	 * $i=0;
	 * $arrPersoLieu = $perso->getLieu()->getPerso($perso, $i++);
	 * foreach($arrPersoLieu as $arrPerso)
	 *	$persoDansLeLieuActuel = new Perso($arrPerso);
	 * </code>
	 *
	 * @param mixed $arr Id (int) du joueur OU tableau (array) d'information
	 */ 
	function __construct(&$arr=array())
	{
		//Certains modules instancie la classe perso pour obtenir certaines listes
		//Elle ne créer pas un perso complet.
		if(empty($arr))
			return;
		
		$this->id			= (int)$arr['id'];
		$this->userId		= (int)$arr['userId'];
		$this->pa			= (int)$arr['pa'];
		$this->paMax		= (int)$arr['pamax'];
		$this->pv			= (int)$arr['pv'];
		$this->lastSavedPv	= $this->pv;
		$this->pvMax		= (int)$arr['pvmax'];
		$this->pn			= (int)$arr['pn'];
		$this->pnMax		= 99;
		$this->pr			= 0;
		$this->prMax		= (int)$arr['prmax'];
		$this->sexe			= (string)$arr['sexe'];
		$this->age			= (int)$arr['age'];
		$this->taille		= (string)$arr['taille'];
		$this->ethnie		= stripslashes($arr['ethnie']);
		$this->yeux			= stripslashes($arr['yeux']);
		$this->cheveux		= stripslashes($arr['cheveux']);
		$this->cash			= (int)$arr['cash'];
		$this->background	= stripslashes($arr['background']);
		$this->description	= stripslashes($arr['description']);
		$this->currentAction= stripslashes($arr['current_action']);
		$this->noteMj		= stripslashes($arr['note_mj']);
		$this->avatar		= (string)$arr['imgurl'];
		$this->type			= (string)$arr['playertype'];
		$this->inscrValide	= (string)$arr['inscription_valide']; //0, 1 ou mod
		$this->bloque		= $arr['bloque'] == '1' ? true : false;
		$this->soin			= $arr['soin'];
		$this->menotte		= $arr['menotte']; //InvId des menottes
		$this->esquive		= $arr['esquive'] == '1' ? true : false;
		$this->reaction		= (string)$arr['reaction'];
		$this->lieu			= (string)$arr['lieu']; //L'objet Lieu chargé sur demande
		$this->inventaire	= NULL; //Chargé sur demande
		$this->stat			= array(); //Chargé sur demande
		$this->comp			= array(); //Chargé sur demande
		$this->lng1			= (string)$arr['lng1'];
		$this->lng1_lvl		= (string)$arr['lng1_lvl'];
		$this->lng2			= (string)$arr['lng2'];
		$this->lng2_lvl		= (string)$arr['lng2_lvl'];
		$this->heQte		= (int)$arr['heQte'];
		$this->visaPerm		= stripslashes($arr['visa_perm']);
		
		if (!empty($arr['nom']))
			$this->nom		= stripslashes($arr['nom']);
		else
			$this->nom		= ($this->sexe==='f') ? 'Inconnue' : 'Inconnu';
		
	}
	
	
	
	/**
	 * Charger un nouveau personnage en fonction de son ID
	 * 
	 * @param int $id Id du personnage à charger
	 * @return Member_Perso|false Instance du personnage
	 */
	public static function load($id)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Charger le personnage demandé
		$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'perso'
					. ' WHERE id=:persoId'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$id,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if ($arr===false)
		{
			throw new Exception('Le personnage demandé est inexistant (supprimé).');
			return false;
		}
		
		return new Member_Perso($arr);
	}


	
	
	
	
	/** 
	 * Trouve le niveau d'abonnement d'un personnage
	 *
	 * Note: À utiliser UNIQUEMENT pour trouver le niveau des joueurs externes ( quand $account n'est pas instancié )
	 *           La méthode {@link Account::getMemberLevel()} est préférable.
	 * Exemple d'utilisation - Afficher le type d'abonnement du joueur #801
	 * <code>
	 * switch(Perso::memberLevel(801))
	 * {
	 *	case '0': echo 'membre gratuit';  break;
	 *	case '1': echo 'niveau 1';  break;
	 *	case '2': echo 'niveau 2'; break;
	 *	case '3': echo 'niveau 3'; break;
	 * }
	 * </code>
	 *
	 * @see Account::getMemberLevel()
	 * @param int $id Id du joueur
	 * @return int
	 */ 
	public static function memberLevel($id)
	{ 
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if(!is_numeric($id))
		{
			throw new Exception('Id non numérique.');
			return false;
		}
			
		$query = 'SELECT a.mp'
					. ' FROM ' . DB_PREFIX . 'perso as p'
					. ' INNER JOIN ' . DB_PREFIX . 'account as a ON (a.id = p.userId)'
					. ' WHERE p.id=:persoId'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$id,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		return (int)$arr[0];
	}
	
	
	
	
	/**
	 * Charge la liste des items en inventaire.
	 *
	 * Selon le perso actuel (de l'objet perso), cette fonction créer/génère le 'tableau des item en inventaire'.
	 * Cette fonction est apellée par getInventaire() et par les codes qui modifient l'inventaire (Inventaire, dépouillement, etc) dans le cas ou le 'tableau des item en inventaire' est innexistant.
	 *
	 * @param bool $listAllDrugs Liste les drogues actuellement consommées.
	 * @see getInventaire()
	 */ 
	public function refreshInventaire($listAllDrugs=false)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Vider l'inventaire
		$this->inventaire = NULL;
		
		//Charger le nouvel inventaire
		$this->inventaire = Member_ItemFactory::createFromPersoId($this->getId(), $listAllDrugs);
		
		//Calcul des PR
		$this->pr =0;
		foreach($this->inventaire as $item)
		{
			if(!$item->isEquip())
				$this->pr += $item->getPr()*$item->getQte();
		}
	}
	

	
	
	/**
	 * Charge l'inventaire du personnage.
	 *
	 * Fonctionne pour le personnage actuel. Retournera les 
	 * informations sur les items.
	 *
	 * Exemple d'utilisation - Afficher des items de type badge
	 * <code>
	 * $i=0; $e=0;
	 * while( $item = $perso->getInventaire($i++))
	 *	if($item instanceof Member_ItemBadge)
	 *		$badgeEnPossessionDuPerso[$e++] = $item;
	 * </code>
	 * 
	 * @param int $id Indexe de l'item en inventaire (dans le tableau) à retourner
	 * @param bool $listAllDrugs Liste les drogues actuellement consommées.
	 * @return Member_Item|mixed|false Selon le type d'item
	 */
	public function getInventaire($id, $listAllDrugs=false)
	{ 
		if(!is_numeric($id))
		{
			throw new Exception('Id non numérique.');
			return false;
		}
		
		//Vérifier si les items sont chargés
		if ($this->inventaire===NULL)
			$this->refreshInventaire($listAllDrugs);
			
		//Vérifier si l'index de l'item demandé est hors de la liste existante
		if ($id>=count($this->inventaire))
			return false;
		
		return $this->inventaire[$id];
	}
	
	
	/**
	 * Génère la liste des actions possible du menu d'action.
	 * 
	 * Se base sur les items en inventaire, le lieu actuel et le personnage
	 *
	 * Exemple de retour
	 * <code>
	 * array(
	 * 	array('url' => '-',					'txt' => 'Lieu'), //Séparateur
	 * 	array('url' => 'Perso_Deplacement',	'txt' => 'Déplacement')
	 * );
	 * </code>
	 *
	 * @return array Tableau des actions
	 */ 
	public function generateActionMenu()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		// GÉNÉRER le menu en rapport avec le LIEU
		$menu[] = array('url' => '-',							'txt' => 'Lieu'); //Séparateur
		$menu[] = array('url' => 'Perso_Deplacement',			'txt' => 'Déplacement');
		
		//Si le personnage n'est pas dans une "pièce de contension système"
		if ( $this->getLieu()->getNomTech() != INNACTIVITE_VOLUNTARY_LOCATION 
				&&
			 $this->getLieu()->getNomTech() != INNACTIVITE_TELEPORT_LOCATION){
			
			// GÉNÉRER le menu en rapport avec le LIEU
				$menu[] = array('url' => 'Perso_FouillerLieu',	'txt' => 'Fouiller le lieu');
				$menu[] = array('url' => 'Perso_Coupdoeil',		'txt' => 'Jeter un coup d\'oeil');
				
				
				//Lister les actions personnalisables
				$query = 'SELECT caption,url'
							. ' FROM ' . DB_PREFIX . 'lieu_menu'
							. ' WHERE lieutech=:nomTech;';
				$prep = $db->prepare($query);
				$prep->bindValue(':nomTech',	$this->getLieu()->getNomTech(),	PDO::PARAM_INT);
				$prep->executePlus($db, __FILE__,__LINE__);
				$arrLieuMenu = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;

				
				foreach($arrLieuMenu as &$arr)
					$menu[] = array('url' => 'Lieu_' . $arr['url'], 'txt' => $arr['caption']);
			
			
				// GÉNÉRER le menu en rapport avec le PERSO
				$menu[] = array('url' => '-',						'txt' => 'Personnage'); //Séparateur
				$menu[] = array('url' => 'Perso_Attaquer',			'txt' => 'Attaquer');
				$menu[] = array('url' => 'Perso_Depouiller',		'txt' => 'Fouiller une personne');
				$menu[] = array('url' => 'Perso_Parler',			'txt' => '&gt; PARLER &lt;');
				$menu[] = array('url' => 'Perso_Soigner',			'txt' => 'Soigner');
				$menu[] = array('url' => 'Perso_Ficheperso',		'txt' => 'Fiche Perso');
		
		
				// GÉNÉRER le menu en rapport avec les ITEMS
				$menu[] = array('url' => '-',						'txt' => 'Matériel'); //Séparateur
				$menu[] = array('url' => 'Perso_Donnercash',		'txt' => 'Donner argent');
				$menu[] = array('url' => 'Perso_Donneritem',		'txt' => 'Donner item');
				$menu[] = array('url' => 'Perso_Inventaire',		'txt' => 'Gestion de l\'inventaire');
				
				//Lister les actions personnalisables
				$URLarr = array();
				$query = 'SELECT caption, url'
							. ' FROM ' . DB_PREFIX . 'item_inv'
							. ' LEFT JOIN ' . DB_PREFIX . 'item_menu ON (item_dbid = inv_dbid)'
							. ' WHERE inv_persoid=:persoId;';
				$prep = $db->prepare($query);
				$prep->bindValue(':persoId',	(int)$this->getId(),	PDO::PARAM_INT);
				$prep->executePlus($db, __FILE__,__LINE__);
				$arrItemMenu = $prep->fetchAll();
				$prep->closeCursor();
				$prep = NULL;

				
				foreach($arrItemMenu as &$arr)
				{
					$present = false;
					foreach($URLarr as $elem)
					{
						if ($elem==$arr['url'])
						{
							$present=true;
							break;
						}
					}
					
					if (!$present && !empty($arr['url']))
					{
						$URLarr[]=$arr['url'];
						$menu[] = array('url' => 'Item_' . $arr['url'],	'txt' => $arr['caption']);
					}
				}
		}
		
		return $menu;
	}
	
	
	
	/**
	 * Retourne l'item de type arme qui est équipé.
	 *
	 * Exemple d'utilisation - Afficher des items de type badge
	 * <code>
	 * echo "Vous êtes armé avec un(e): " . $perso->getArme()->getNom();
	 * </code>
	 *
	 * @see Member_ItemArme
	 * @return Member_ItemArme|mixed Selon le type d'item
	 */
	public function getArme()
	{ 
	
		if (empty($this->arme))
		{
			$i=0; $e=0;
			while( $item = $this->getInventaire($i++))
				if($item instanceof Member_ItemArme)
					if($item->isEquip())
						$this->arme = $item;
			
			if(empty($this->arme) || $this->getMenotte())
				$this->arme = new Member_ItemArmeMainsnues();
		}
		
		//Si aucune arme équipé, retourner Mains Nues
		return $this->arme;
	}
	
	
	
	/** 
	 * Vérifie si le perso est légé (suffisamment de PR).
	 *
	 * @return bool
	 */
	public function isLege()
	{
		return ($this->prMax - $this->pr) > 2 ? true : false;
	}

	
	
	/** 
	 * Vérifie si le perso est rassasié (suffisamment de PN).
	 *
	 * @return bool
	 */
	public function isRassasie()
	{
		return $this->pn >= 80 ? true : false;
	}
	
	/** 
	 * Vérifie si le perso a faim (trop peu de PN).
	 *
	 * @return bool
	 */
	public function isFaim()
	{
		return $this->pn < 30 ? true : false;
	}

	
	
	/** 
	 * Vérifie si le perso est en forme (suffisamment de PA).
	 *
	 * @return bool
	 */
	public function isEnergetique()
	{
		return $this->pa >= 30 ? true : false;
	}
	
	/** 
	 * Vérifie si le perso est faible (au moins un peu de PA).
	 *
	 * @return bool
	 */
	public function isFaible()
	{
		return $this->pa > 0 ? true : false;
	}

	
	/** 
	 * Vérifie si le perso est mort.
	 *
	 * @return bool
	 */
	public function isVivant()
	{
		return $this->pv > 0 ? true : false;
	}
	
	/** 
	 * Vérifie si le perso est conscient.
	 *
	 * @return bool
	 */
	public function isConscient()
	{
		return $this->pv >= 10 ? true : false;
	}
	
	/**
	 * Vérifie si le perso est en état de marcher.
	 *
	 * @return bool
	 */
	public function isAutonome()
	{
		return $this->pv >= 25 ? true : false;
	}
	
	/** 
	 * Vérifie si le perso est en bonne condition physique.
	 *
	 * @return bool
	 */
	public function isNormal()
	{
		return $this->pv >= 70 ? true : false;
	}
	
	/**
	 * Retourne le coeff de soin (du lieu) nécessaire pour soigner.
	 *
	 * Signification:
	 * - 0 = Soin de blessés légés (le personnage est autonome)
	 * - 1 = Soin des non-autonomes (le personnage est conscient)
	 * - 2 = Soin des inconscients (le personnage est vivant)
	 * - 3 = Ré-animation possible (le personnage est mort)
	 *
	 * @return int
	 */
	public function getCoeffSoinNecessaire()
	{	
		if($this->isAutonome())
			return 0;
		elseif($this->isConscient())
			return 1;
		elseif($this->isVivant())
			return 2;	
		else
			return 3;
	}
	
	/**
	 * Retourne l'Id du personnage.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * Retourne le nombre de Points d'Action.
	 * 
	 * @return int
	 */
	public function getPa()
	{
		return $this->pa;
	}
	
	/**
	 * Retourne le maximum de Points d'Action.
	 *
	 * @return int
	 */
	public function getPaMax()
	{
		return $this->paMax;
	}
	
	/**
	 * Retourne le nombre de Points de Nourriture.
	 *
	 * @return int
	 */
	public function getPn()
	{
		return $this->pn;
	}
	
	/**
	 * Retourne le maximum de Points de Nourriture.
	 *
	 * @return int
	 */
	public function getPnMax()
	{
		return $this->pnMax;
	}
	
	/** 
	 * Retourne le nombre de Points de Vie.
	 *
	 * @return int
	 */
	public function getPv()
	{
		return $this->pv;
	}
	
	/**
	 * Retourne le maximum de Points de Vie.
	 * 
	 * @return int
	 */
	public function getPvMax()
	{
		return $this->pvMax;
	}
	
	/**
	 * Retourne le nombre de Points de Rangement.
	 * 
	 * @return int
	 */
	public function getPr()
	{
		//Vérifier si les items sont chargés
		if ($this->inventaire===NULL)
			$this->refreshInventaire();
			
		return $this->pr;
	}
	
	/**
	 * Retourne le maximum de Points de Rangement.
	 *
	 * @return int
	 */
	public function getPrMax()
	{
		return $this->prMax;
	}
	
	/**
	 * Retourne le nom affichable du personnage.
	 *
	 * @return string
	 */
	public function getNom()
	{
		return $this->nom;
	}
	
	/**
	 * Retourne la description des yeux du personnage.
	 *
	 * @return string
	 */
	public function getYeux()
	{
		return $this->yeux;
	}
	
	/**
	 * Retourne le nom affichablea description des cheveux du personnage.
	 *
	 * @return string
	 */
	public function getCheveux()
	{
		return $this->cheveux;
	}	
	/**
	 * Retourne l'ethnie du personnage.
	 *
	 * @return string
	 */
	public function getEthnie()
	{
		return $this->ethnie;
	}
	
	
	/**
	 * Retourne le id du compte associé au personnage.
	 *
	 * @return int
	 */
	public function getUserId()
	{
		return $this->userId;
	}
	
	
	/**
	 * Retourne le lieu dans lequel le personnage se trouve.
	 *
	 * @see Member_Lieu
	 * @return Member_Lieu
	 */
	public function getLieu()
	{
		//Si le lieu n'est que le nom technique, charger l'objet
		try
		{
			if(!$this->lieu instanceof Member_Lieu)
				$this->lieu	= Member_LieuFactory::createFromNomTech($this->lieu);
			return $this->lieu;
		}
		catch(Exception $e)
		{
			throw $e;
			return null;
		}
	}
	
	/**
	 * Retourne le montant d'argent (liquide) que le personnage possède sur lui.
	 *
	 * @return float
	 */
	public function getCash()
	{
		return $this->cash;
	}
	
	/**
	 * Retourne le sexe du personnage.
	 *
	 * Légende:
	 * 'm' = homme
	 * 'f' = femme
	 * 
	 * @return string
	 */
	public function getSexe()
	{
		return $this->sexe;
	}
	
	/**
	 * Retourne l'age du personnage.
	 *
	 * @return int
	 */
	public function getAge()
	{
		return $this->age;
	}
	
	/**
	 * Retourne la taille du personnage au format "1m82".
	 *
	 * @return string
	 */
	public function getTaille()
	{
		return $this->taille;
	}
	
	/** 
	 * Vérifie si un perso a déjà eu des soins.
	 *
	 * @return bool
	 */
	public function getSoin()
	{
		if(!is_numeric($this->soin))
			return false;

		return $this->soin==1 ? true : false;
	}
	
	/**
	 * Vérifie si un perso est menotté ou pas.
	 * 
	 * Retournera false si le perso est libre ou le inv_id des
	 * menottes qui le retiennent dans le cas contraire.
	 *
	 * @return int|false
	 */
	public function getMenotte()
	{
		if(!is_numeric($this->menotte))
			return false;
		
		return $this->menotte>0 ? $this->menotte : false;
	}
	
	/**
	 * Retourne la description physique du personnage.
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}
	
	/**
	 * Retourne le code de la langue1
	 */
	public function getLangue1Code()
	{
		return $this->lng1;
	}
	
	/**
	 * Retourne le code de la langue2
	 */
	public function getLangue2Code()
	{
		return $this->lng2;
	}
	
	/**
	 * Retourne le code de niveau de la langue1
	 */
	public function getLangue1LvlCode()
	{
		return $this->lng1_lvl;
	}
	
	/**
	 * Retourne le code de niveau de la langue2
	 */
	public function getLangue2LvlCode()
	{
		return $this->lng2_lvl;
	}
	
	/**
	 * Converti un code de langue en langue.
	 *
	 * @param string $code Code de la langue (Ex.: en)
	 * @return string Langue affichable (Ex.: anglais)
	 */
	public static function convCodeLngToTxt($code)
	{
		$arrLangues = array(
				'af' => 'afrikaans',
				'al' => 'albanais',
				'ge' => 'allemand',
				'am' => 'amharique',
				'en' => 'anglais',
				'ar' => 'arabe',
				'an' => 'arménien',
				'az' => 'azéri',
				'ba' => 'bengali',
				'bl' => 'biélorusse',
				'bo' => 'bosniaque',
				'bu' => 'bulgare',
				'ca' => 'catalan',
				'cc' => 'chinois (cantonais)',
				'cm' => 'chinois (mandarin)',
				'si' => 'cinghalais',
				'ko' => 'coréen',
				'cr' => 'croate',
				'da' => 'danois',
				'sp' => 'espagnol',
				'es' => 'estonien',
				'fa' => 'féroen',
				'fi' => 'finnois',
				'fl' => 'flamand',
				'fr' => 'français',
				'ir' => 'gaélique (irlandais)',
				'we' => 'gallois',
				'gn' => 'géorgien',
				'gk' => 'grec',
				'he' => 'hébreu',
				'hi' => 'hindi',
				'hu' => 'hongrois',
				'in' => 'indonésien',
				'ic' => 'islandais',
				'it' => 'italien',
				'ja' => 'japonais',
				'ka' => 'kazakh',
				'kh' => 'khmer',
				'ki' => 'kirghiz',
				'lo' => 'lao',
				'la' => 'letton',
				'li' => 'lithuanien',
				'lu' => 'luxembourgeois',
				'ma' => 'macédonien',
				'my' => 'maltais',
				'ml' => 'maltese',
				'mo' => 'moldave',
				'mn' => 'mongol',
				'du' => 'néerlandais',
				'ne' => 'népali',
				'no' => 'norvégien',
				'ur' => 'ourdou',
				'ps' => 'persan',
				'pl' => 'polonais',
				'po' => 'portugais',
				'ro' => 'roumain',
				'ru' => 'russe',
				'sa' => 'samoan',
				'se' => 'serbe',
				'sl' => 'slovaque',
				'sn' => 'slovène',
				'sw' => 'suédois',
				'ta' => 'tagalog',
				'tj' => 'tadjik',
				'tm' => 'tamoul',
				'cz' => 'tchèque',
				'th' => 'thaï',
				'tu' => 'turc',
				'uk' => 'ukrainien',
				'yi' => 'yiddish'
			);
		$code = strtolower($code);
		
		if(isset($arrLangues[$code]))
			return $arrLangues[$code];
			
		return false;
	}
	
	/**
	 * Converti un code de maitrise d'une langue en mot.
	 *
	 * @param string $code Code de maitrise (Ex.: cu)
	 * @return string Niveau de maitrise affichable (Ex.: compris uniquement)
	 */
	public static function convCodeMaitriseLngToTxt($code)
	{
		$arr = array(
				'cu' => 'compris uniquement',
				'pe' => 'parlé & écrit',
				'co' => 'courant',
				'bi' => 'bilingue',
				'lm' => 'langue maternelle'
			);
		$code = strtolower($code);
		
		if(isset($arr[$code]))
			return $arr[$code];
			
		return false;			
	}
	
			
	/**
	 * Retourne le background du personnage.
	 *
	 * @return string
	 */
	public function getBackground()
	{
		return $this->background;
	}
	
	/**
	 * Retourne la note des MJ du personnage.
	 *
	 * @return string
	 */
	public function getNoteMj()
	{
		return $this->noteMj;
	}
	
	/**
	 * Retourne la description de l'action courrante du personnage.
	 *
	 * @return string
	 */
	public function getCurrentAction()
	{
		return $this->currentAction;
	}
	
	/**
	 * Change la description de l'action courrante du personnage.
	 *
	 */
	public function setCurrentAction($currentAction)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'UPDATE ' . DB_PREFIX . 'perso'
				. ' SET current_action=:current_action'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$esquive = ((isset($_POST['esquive'])) ? '1' : '0');
		$prep->bindValue(':current_action',	$currentAction,	PDO::PARAM_STR);
		$prep->bindValue(':id',				$this->id,			PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__,__LINE__);
	}
	
	/** 
	* Retourne l'URL de l'avatar du personnage.
	*
	* @return string
	*/
	public function getAvatar()
	{
		return $this->avatar;
	}
	
	/**
	* Vérifie si l'inscription est validée.
	*
	* @return bool
	*/
	public function isInscriptionValide()
	{
		return $this->inscrValide=='1' ? true : false;
	}
	
	/**
	 * Vérifie si le perso est actuellement bloqué par un MJ.
	 *
	 * @return bool
	 */
	public function isBloque()
	{
		return $this->bloque;
	}
	
	/**
	 * Valide si le personnage essaie d'esquiver les attaques.
	 * Si non-autonome, l'esquive est impossible.
	 * @return bool
	 */
	public function getEsquive()
	{
		return $this->isAutonome() ? $this->esquive : false;
	}
	
	
	/**
	 * Retourne le type de personnage (généralement 'humain')
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}
	
	
	/**
	 * Retourne le mode de riposte aux attaques.
	 *
	 * Légende:
	 * - 'rien' = Aucune riposte
	 * - 'riposte' = Riposte
	 * - 'fuir' = Essayer de fuir
	 * 
	 * @return string
	 */
	public function getReaction()
	{
		return $this->reaction;
	}
	
	/**
	 * Modifier le montant d'argent que le perso possède sur lui.
	 *
	 * Cette méthode s'occupe de calculer elle même le solde.
	 * IMPORTANT: pour sauvegarder en DB les modifications,
	 * vous DEVEZ apeller {@link setCash()}.
	 *
	 * @see setCash()
	 * @param char $plusMoins Prend la valeur '+' ou '-' selon si on désire ajouter ou retirer de l'argent
	 * @param float $montant Montant d'argent  à ajouter ou retirer
	 */
	public function changeCash($plusMoins, $montant)
	{
		if(($plusMoins!='+' && $plusMoins!='-') || !is_numeric($montant))
		{
			throw new Exception('InvalidOperator');
			return false;
		}
		
		$montant = floor($montant); //Éviter les virgules
		
		if($plusMoins=='+')
			$this->cash+=$montant;
		elseif($plusMoins=='-')
			$this->cash-=$montant;

		if($this->cash<0)
			$this->cash=0;
	}
	
	/**
	 * Modifier le nombre de PA du perso.
	  * 
	 * Cette méthode s'occupe de calculer elle même le total et
	 * s'assure que paMax n'est pas dépassé.
	 * La fonction {@link boostPa()} permet de dépasser paMax.
	 * IMPORTANT: pour sauvegarder en DB les modifications,
	 * vous DEVEZ apeller {@link setPa()}.
	 *
	 * @see setPa()
	 * @param char $plusMoins Prend la valeur '+' ou '-' selon si on désire ajouter ou retirer des PA
	 * @param float $nbrPa Nombre de PA à ajouter ou retirer
	 */
	public function changePa($plusMoins, $nbrPa)
	{
		if(($plusMoins!='+' && $plusMoins!='-') || !is_numeric($nbrPa))
		{
			throw new Exception('InvalidOperator');
			return false;
		}
		
		if($plusMoins=='+')
		{
			if($this->pa < $this->paMax)
				if( ($this->pa + $nbrPa) <= $this->paMax)
					$this->pa+=$nbrPa;
				else
					$this->pa = $this->paMax;
		}
		elseif($plusMoins=='-')
			$this->pa-=$nbrPa;

		if($this->pa<0)
			$this->pa=0;
		
	}

	/**
	 * Modifier le nombre de PA du perso.
	 *
	 * Equivalent de {@link changePa()} mais permet de dépasser paMax.
	 * La fonction borne les pa à 0 en cas de résultat négatif.
	 * IMPORTANT: pour sauvegarder en DB les modifications,
	 * vous DEVEZ apeller {@link setPa()}.
	 *
	 * @see setPa()
	 * @param char $plusMoins Prend la valeur '+' ou '-' selon si on désire ajouter ou retirer des PA
	 * @param float $nbrPa Nombre de PA à ajouter ou retirer
	 */
	public function boostPa($plusMoins, $nbrPa)
	{
		if(($plusMoins!='+' && $plusMoins!='-') || !is_numeric($nbrPa))
		{
			throw new Exception('InvalidOperator');
			return false;
		}

		if($plusMoins=='+')
		{
			$this->pa+=$nbrPa;
		}
		elseif($plusMoins=='-')
			$this->pa-=$nbrPa;

		if($this->pa<0)
			$this->pa=0;

	}

	/**
	 * Modifier le nombre de PN du perso.
	 * 
	 * Cette méthode s'occupe de calculer elle même le total et
	 * s'assure que pnMax n'est pas dépassé.
	 * IMPORTANT: pour sauvegarder en DB les modifications,
	 * vous DEVEZ apeller {@link setPn()}.
	 *
	 * @see setPn()
	 * @param char $plusMoins Prend la valeur '+' ou '-' selon si on désire ajouter ou retirer des PN
	 * @param float $nbrPa Nombre de PN à ajouter ou retirer
	 */
	public function changePn($plusMoins, $nbrPn)
	{
		if(($plusMoins!='+' && $plusMoins!='-') || !is_numeric($nbrPn))
		{
			throw new Exception('InvalidOperator');
			return false;
		}
		
		if($plusMoins=='+')
		{
			if($this->pn < $this->pnMax)
				if( ($this->pn + $nbrPn) <= $this->pnMax)
					$this->pn+=$nbrPn;
				else
					$this->pn = $this->pnMax;
		}
		elseif($plusMoins=='-')
			$this->pn-=$nbrPn;

		if($this->pn<0)
			$this->pn=0;
			
	}
	
	/**
	 * Modifier le nombre de PV du perso.
	 *
	 * Cette méthode s'occupe de calculer elle même le total et
	 * s'assure que pvMax n'est pas dépassé.
	 * Pour dépasser pvMax voir {@link boostPv()}
	 * IMPORTANT: pour sauvegarder en DB les modifications,
	 * vous DEVEZ apeller {@link setPv()}.
	 *
	 * @see setPv()
	 * @param char $plusMoins Prend la valeur '+' ou '-' selon si on désire ajouter ou retirer des PV
	 * @param float $nbrPv Nombre de PV à ajouter ou retirer
	 */
	public function changePv($plusMoins, $nbrPv)
	{
		if(($plusMoins!='+' && $plusMoins!='-') || !is_numeric($nbrPv))
		{
			throw new Exception('InvalidOperator');
			return false;
		}
		
		if($plusMoins=='+')
		{
			if($this->pv < $this->pvMax)
				if( ($this->pv + $nbrPv) <= $this->pvMax)
					$this->pv+=$nbrPv;
				else
					$this->pv = $this->pvMax;
		}
		elseif($plusMoins=='-')
			$this->pv-=$nbrPv;

		if($this->pv<0)
			$this->pv=0;
			
	}
	
	/**
	 * Modifier le nombre de PV du perso.
	 *
	 * Equivalent de {@link changePv()} mais permet de dépasser pvMax.
	 * La fonction borne à 0 en cas de résultat négatif.
	 * IMPORTANT: pour sauvegarder en DB les modifications,
	 * vous DEVEZ apeller {@link setPv()}.
	 *
	 * @see setPv()
	 * @param char $plusMoins Prend la valeur '+' ou '-' selon si on désire ajouter ou retirer des PV
	 * @param float $nbrPv Nombre de PV à ajouter ou retirer
	 */
	public function boostPv($plusMoins, $nbrPv)
	{
		if(($plusMoins!='+' && $plusMoins!='-') || !is_numeric($nbrPv))
		{
			throw new Exception('InvalidOperator');
			return false;
		}

		if($plusMoins=='+')
		{
			$this->pv+=$nbrPv;
		}
		elseif($plusMoins=='-')
			$this->pv-=$nbrPv;

		if($this->pv<0)
			$this->pv=0;
	}
	
	/**
	 * Modifier directement le montant que le perso possède sur lui.
	 * 
	 * Cette méthode modifie directement le solde du perso.
	 * NOTE: La méthode {@link changeCash()} permet d'effectuer
	 * automatiquement les calculs de solde.
	 *
	 * @see changeCash()
	 * @param float $montant Montant auquel placer le solde du compte
	 */
	public function setCash($montant = false)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if($montant === false)
			$montant = $this->cash;
		
		//Supprimer les décimales
		$montant = floor($montant); 
		
		$query = 'UPDATE ' . DB_PREFIX . 'perso'
				. ' SET cash=:cash'
				. ' WHERE id=:persoId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':cash',		$montant,	PDO::PARAM_INT);
		$prep->bindValue(':persoId',	$this->id,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		$this->cash = $montant;
	}
	
	/**
	 * Modifier directement le nombre de PA du perso.
	 *
	 * Cette méthode modifie directement le total.
	 * NOTE: La méthode {@link changePa()} permet d'effectuer
	 * automatiquement les calculs du total.
	 *
	 * @see changePa()
	 * @param float $nbrPa Nombre de PA du perso
	 */
	public function setPa($nbrPa = false)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if($nbrPa === false)
			$nbrPa = $this->getPa();
		
		$query = 'UPDATE ' . DB_PREFIX . 'perso'
				. ' SET pa=:pa'
				. ' WHERE id=:persoId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':pa',			$nbrPa,		PDO::PARAM_INT);
		$prep->bindValue(':persoId',	$this->id,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		$this->pa = $nbrPa;
	}
	
	/**
	 * Modifier directement le nombre de PN du perso.
	 *
	 * Cette méthode modifie directement le total.
	 * NOTE: La méthode {@link changePn()} permet d'effectuer
	 * automatiquement les calculs du total.
	 *
	 * @see changePn()
	 * @param float $nbrPn Nombre de PN du perso
	 */
	public function setPn($nbrPn = false)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if($nbrPn === false)
			$nbrPn = $this->getPn();
		
		$query = 'UPDATE ' . DB_PREFIX . 'perso'
				. ' SET pn=:pn'
				. ' WHERE id=:persoId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':pn',			$nbrPn,		PDO::PARAM_INT);
		$prep->bindValue(':persoId',	$this->id,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		$this->pn = $nbrPn;
	}
	
	/**
	 * Modifier directement le nombre de PV du perso.
	 * 
	 * Cette méthode modifie directement le total.
	 * - NOTE: La méthode {@link changePv()} permet d'effectuer
	 * automatiquement les calculs du total.
	 *
	 * - NOTE: Les 2 premiers paramètres servent au log de la mort
	 * d'un personnage.
	 *
	 * @see changePv()
	 * @param Member_Perso|string &$from Personnage qui provoque le changement
	 * @param string $action Action qui provoque le changement
	 * @param float $nbrPv Nombre de PV du perso
	 */
	public function setPv($from, $action, $nbrPv = false)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		if($nbrPv === false)
			$nbrPv = $this->getPv();
		
		
		//Log si une mort surviens
		if($nbrPv <= 0 && $this->lastSavedPv > 0)
			$this->logMort($from, $action);
		
		
		//Changement des PV
		$query = 'UPDATE ' . DB_PREFIX . 'perso '
					. ' SET pv=:pv'
					. ' WHERE id=:persoId'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':pv',			$nbrPv,		PDO::PARAM_INT);
		$prep->bindValue(':persoId',	$this->id,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		$this->pv = $nbrPv;
		$this->lastSavedPv = $nbrPv;
	}
	
	
	
	/**
	 * Renseigner la mort du perso dans les logs.
	 * 
	 * Apellée par MJ_Perso_Mod ou Member_Perso::setPv.
	 *
	 * @param Member_Perso|string &$from Personnage qui provoque la mort.
	 * @param string $action Action qui provoque la mort.
	 * @param string $persoNom Nom du personnage.
	 */
	public function logMort($from, $action)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if($from instanceof Member_Perso)
		{
			$fromId		= $from->getId();
			$fromNom	= $from->getNom();
		}
		else //Dans le cas des remises
		{
			
			$fromId		= 0;
			$fromNom	= $from;
		}
		
		
		
		
		$query = 'INSERT INTO `' . DB_PREFIX . 'log_persomort`'
					. ' (`perso`,`persoId`,`timestamp`,`from`,`fromId`,`action`)'
					. ' VALUES'
					. ' (:nom, :persoId, UNIX_TIMESTAMP(), :from, :fromId, :action);';
		$prep = $db->prepare($query);
		$prep->bindValue(':nom',		$this->nom,		PDO::PARAM_STR);
		$prep->bindValue(':persoId',	$this->id,		PDO::PARAM_INT);
		$prep->bindValue(':from',		$fromNom,		PDO::PARAM_STR);
		$prep->bindValue(':fromId',		$fromId,		PDO::PARAM_INT);
		$prep->bindValue(':action',		$action,		PDO::PARAM_STR);
		$prep->executePlus($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//###STAT : Gestion des statistiques
	
	/**
	 * Charge les statistiques si non-chargées
	 * 
	 * @return void
	 */
	private function checkStatLoad()
	{
		if(empty($this->stat))
			$this->loadStat();
	}
	
	
	/**
	 * Charge les stats.
	 */
	private function loadStat()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		//Chargement des statistiques brutes
		$query = 'SELECT s.*, p.xp'
				. ' FROM ' . DB_PREFIX . 'stat as s'
				. ' LEFT JOIN ' . DB_PREFIX . 'perso_stat as p'
					. ' ON (p.statid = s.id AND p.persoid=:persoId)'
				. ' ORDER BY s.nom;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$this->id,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__,__LINE__);
		$arrStat = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		foreach($arrStat as &$arr)
		{
			$id = $arr['id'];
			
			//Si aucun XP est défini, c'est que la statistique n'existe pas encore pour le perso
			//if(empty($arr['xp']) && $arr['xp']!==0)
				//	$arr['xp'] = null;
			
			$arr['drug_multi'] = 0.0; //Aucune drogue, aucun effet, multiplicateur de 0
			$this->stat[$id] = $arr;
		}
		
		
		//Calcul des statistiques modifiées
		//Lister les drogues en inventaire
		$drogues = array();
		$i=0;
		while( $item = $this->getInventaire($i++, true))
			if($item instanceof Member_ItemDrogueDrogue)
				if($item->isEquip()) //Si consommé
					$drogues[] = $item;
			
		
		
		//Si la personne à consomé une drogue
		if (count($drogues)>0)
		{
			//Calculer le % selon la portion de la substance dans la drogue
			//Pour chaque drogue, pour chaque stat, additionner l'effet
			foreach($drogues as $drogue)
			{
				foreach($this->stat as &$stat)
				{
					$statPerc = $drogue->getPercStat($this->getStatCode($stat['id']))/100;
					//if($stat['xp']<0) //Si la stat est négative, il faut inverser l'effet de la drogue
					//	$statPerc = 1 / $statPerc ; //0.5 = *2, 2.0 = *0.5
					
					$stat['drug_multi'] += $statPerc; //Ex.: 0.3
				}
			}
		}
		
		return true;
	}
	
	
	/** 
	 * Modifie les statistiques.
	 * 
	 * Note: Cette fonction valide que le total des modification donne ZERO.
	 * Dans le cas ou le total est différent de 0, la fonction retourne un
	 * message d'erreur plutot que la ligne d'information.
	 *
	 * Exemple d'utilisation - Modifier les statistiques du personnage actuel
	 * <code>
	 * $perso->setStat(array('PER' => '+01', 'DEX' => '+01', 'FOR' => '-02' ));
	 * </code>
	 *
	 * @param array Tableau avec 1 instruction par ligne. Code de stat en index, modificateur (d'XP) au format [+-][0-9][0-9] en valeur.
	 * @return string Message d'information pour que le joueur prenne connaissance de ses gains.
	 */
	public function setStat($arr)
	{
		$this->checkStatLoad();
		
		if (!is_array($arr))
		{
			throw new Exception('Tableau attendu.');
			return false;
		}
		
		
		$total = 0;
		$keys = array_keys($arr);
		
		
		
		//Valider les données reçues
		foreach($keys as $key)
		{
			
			$plusMoins 	= substr($arr[$key],0,1);
			$nbr		= substr($arr[$key],1,2);
			
			if ($plusMoins!='+' && $plusMoins!='-')
			{
				throw new Exception("Format non respecté. '+' ou '-' attendu (1chr), '{$plusMoins}' recu.");
				return false;
			}
				
			if (!is_numeric($nbr))
			{
				throw new Exception("Format non respecté. 00 à 99 attendu (2chr), '{$nbr}' recu.");
				return false;
			}
			
			if($plusMoins=='+')
				$total += $nbr;
			else
				$total -= $nbr;
		}
		
		if ($total!=0)
		{
			throw new Exception("Somme à zero non-respectée. 0 attendu, '{$total}' recu.");
			return false;
		}
		
		
		
		//Préparer le message
		/*
		if(!DEBUG_MODE)
		{
			$msg = '';
		}
		else
		{
		// */
			$queryAddon = '';
			$msg = array();
			foreach($keys as $key)
			{
				
				$plusMoins 	= substr($arr[$key],0,1);
				$nbr		= substr($arr[$key],1,2);
				
				try
				{
					$statId = $this->convStatCodeToId($key);
				}
				catch(Exception $e)
				{
					throw $e;
					return false;
				}
				
				if($nbr>0)
					$msg[] = $this->getStatName($statId) . $plusMoins . $nbr;
			}
			$msg = implode(', ', $msg);
		//}
		
		
		//Effectuer les requêtes de modification
		//Normalement tout à déjà été validé.
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		foreach($keys as $key)
		{
			$statId		= $this->convStatCodeToId($key);
			$plusMoins 	= substr($arr[$key],0,1);
			$nbr		= substr($arr[$key],1,2);
			$xp			= $this->getStatRawXp($statId);
			
			if($xp===null)
			{
				if($plusMoins=='+')
					$xp = $nbr;
				else
					$xp = 0-$nbr;
					
				$query = 'INSERT INTO `' . DB_PREFIX . 'perso_stat`'
							. ' (persoid, statid, `xp`)'
							. ' VALUES'
							. ' (:persoId, $statId, $xp);';
			}
			else
			{
				if($plusMoins=='+')
					$xp += $nbr;
				else
					$xp -= $nbr;
			
				$query = 'UPDATE `' . DB_PREFIX . 'perso_stat`'
						. ' SET `xp`=:xp'
						. ' WHERE	statid=:statId'
							. ' AND persoid=:persoId'
						. ' LIMIT 1;';
			}
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId',	$this->id,	PDO::PARAM_INT);
			$prep->bindValue(':statId',		$statId,	PDO::PARAM_INT);
			$prep->bindValue(':xp',			$xp,		PDO::PARAM_INT);
			$prep->executePlus($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;

			$this->stat[$statId]['xp'] = $xp;
			
		}
		
		return $msg;
	}
	
	/** 
	 * Retourne une tableau des id des statistiques du personnage.
	 * array(1,3,4,5)
	 * @return array
	 */
	 public function getStat()
	 {
	 	$this->checkStatLoad();
	 	
	 	$arr = array();
	 	foreach($this->stat as $stat)
	 		$arr[] = $stat['id'];
	 	return $arr;
	 }
	 
	
	
	/** 
	 * Converti un taux d'XP en level. (pour les statistiques)
	 * 
	 * @param int $xp Taux d'XP à convertir
	 * @return int Niveau
	 */
	public static function convStatXpToLevel ( $xp ) 
	{
		if(!is_numeric($xp))
			return 0;
		
		$stat_lvlxp['-5'] = -400;
		$stat_lvlxp['-4'] = -200;
		$stat_lvlxp['-3'] = -100;
		$stat_lvlxp['-2'] = -50;
		$stat_lvlxp['-1'] = -20;
		$stat_lvlxp['0'] = 0;
		$stat_lvlxp['1'] = 20;
		$stat_lvlxp['2'] = 50;
		$stat_lvlxp['3'] = 100;
		$stat_lvlxp['4'] = 200;
		$stat_lvlxp['5'] = 400;
		
		if( $xp > $stat_lvlxp[-1] && $xp < $stat_lvlxp[1])
			return 0;
		
		
		if( $xp >=0 )
		{
			for($i=1;$i<=5;$i++)
				if ($stat_lvlxp[$i]>$xp)
					return ($i-1);
			return 5;
			
		}
		else
		{
			for($i=-1;$i>=-5;$i--)
				if ($stat_lvlxp[$i]<$xp)
					return ($i+1);
			return -5;
			
		}
	}

	
	/**
	 * Converti un level entaux d'XP. (pour les statistiques)
	 * 
	 * @param int $lvl Level à convertir convertir
	 * @return int Nombre d'XP
	 */
	public static function convStatLevelToXp ( $lvl )
	{
		if(!is_numeric($lvl))
			return 0;
		
		$lvl+=5; //-5=0, +5=10
		
		if($lvl<0)
			$lvl=0;
		if($lvl>10)
			$lvl=10;
			
		switch($lvl)
		{
			case 0:		return -400;	break;
			case 1:		return -200;	break;
			case 2:		return -100;	break;
			case 3:		return -50;		break;
			case 4:		return -20;		break;
			case 5:		return 0;		break;
			case 6:		return 20;		break;
			case 7:		return 50;		break;
			case 8:		return 100;		break;
			case 9:		return 200;		break;
			case 10:	return 400;		break;
		}
	}
	
	/**
	 * Retourne l'id d'une Statistique.
	 * 
	 * @param string $code Code technique de la stat (Ex.: 'DEX' )
	 * @return int Id de la stat (Ex.: 1)
	 */
	private function convStatCodeToId($code)
	{
		$this->checkStatLoad();
		
		foreach($this->stat as $stat)
			if(strtolower($stat['abbr'])==strtolower($code))
				return $stat['id'];
				
		throw new Exception('Statistique innexistante (' . $code . ')');
	}
	
	
	/**
	 * Retourne le nom affichable d'une Statistique.
	 * 
	 * @param string $id Id de la stat (Ex.: 1 )
	 * @return string Nom affichable de la stat (Ex.: 'Dextérité')
	 */
	public function getStatName($id)
	{
		$this->checkStatLoad();
		
		if(isset($this->stat[$id]))
			return $this->stat[$id]['nom'];
		
		throw new Exception('Statistique innexistante (' . $id . ')');
	}
	
	
	/**
	 * Retourne le code d'une Statistique.
	 * 
	 * @param string $id Id de la stat (Ex.: 1 )
	 * @return string Code de la stat (Ex.: 'dex')
	 */
	public function getStatCode($id)
	{
		$this->checkStatLoad();
		
		if(isset($this->stat[$id]))
			return strtolower($this->stat[$id]['abbr']);
		
		throw new Exception('Statistique innexistante (' . $id . ')');
	}
	
	/**
	 * Retourne le niveau d'XP non-modifié d'une statistique.
	 *
	 * @param string $id Id de la stat (Ex.: 1 )
	 * @return int Niveau d'XP brute de la  statistique
	 */
	public function getStatRawXp($id)
	{
		$this->checkStatLoad();
		
		if(isset($this->stat[$id]))
			return $this->stat[$id]['xp'];
		
		throw new Exception('Statistique innexistante (' . $id . ')');
	}
	
	
	/**
	 * Retourne le niveau d'XP réel (influencé par les drogues) d'une statistique.
	 *
	 * @param string $id Id de la stat (Ex.: 1 )
	 * @return int Niveau d'XP réel de la  statistique
	 */
	public function getStatRealXp($id)
	{
		$this->checkStatLoad();
		
		if(isset($this->stat[$id]))
			return round($this->stat[$id]['xp']+ abs($this->stat[$id]['xp']) * $this->stat[$id]['drug_multi']);
		
		throw new Exception('Statistique innexistante (' . $id . ')');
	}
	

	/** 
	 * Retourne le niveau non-modifié d'une statistique.
	 * 
	 * @param string $id Id de la stat (Ex.: 1 )
	 * @return int Niveau réel de la stat
	 */
	public function getStatRawLevel($id)
	{
		$this->checkStatLoad();
		
		try
		{
			$xp = $this->getStatRawXp($id);
			$lvl = self::convStatXpToLevel($xp);
		}
		catch(Exception $e)
		{
			throw $e;
			$lvl = 0;
		}
		
		return $lvl;
	}
	
	/** 
	 * Retourne le niveau modifié (par les drogues) d'une statistique.
	 * 
	 * @param string $id Id de la stat (Ex.: 1 )
	 * @return int Niveau réel de la stat
	 */
	public function getStatRealLevel($id)
	{
		$this->checkStatLoad();
		
		try
		{
			$xp = $this->getStatRealXp($id);
			$lvl = self::convStatXpToLevel($xp);
		}
		catch(Exception $e)
		{
			throw $e;
			$lvl = 0;
		}
		
		return $lvl;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//###COMP : Gestion des compétences
	/**
	 * Charge les compétences si non-chargées
	 * 
	 * @return void
	 */
	private function checkCompLoad()
	{
		if(empty($this->comp))
			$this->loadComp();
	}
	
	/**
	 * Charge les comps.
	 */
	private function loadComp()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		//Charger les statistiques
		$this->checkStatLoad();
		
			
		//Chargement des compétences
		$query = 'SELECT c.*, p.xp, s.statid, s.stat_multi'
				. ' FROM ' . DB_PREFIX . 'competence as c'
				. ' LEFT JOIN ' . DB_PREFIX . 'perso_competence as p ON (p.compid = c.id AND p.persoid=:persoId)'
				. ' LEFT JOIN ' . DB_PREFIX . 'competence_stat as s ON (s.compid = c.id)'
				. ' ORDER BY c.nom;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$this->id,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__,__LINE__);
		$arrComp = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$lastId = 0;
		$compMod = array();
		foreach($arrComp as &$arr)
		{
			$compId = $arr['id'];
			
			//S'il s'agit d'une nouvelle compétence
			if($compId != $lastId)
			{
				//Si aucun XP est défini, c'est que la compétence n'existe pas encore pour le perso
				//if(empty($arr['xp']) && $arr['xp']!==0)
				//	$arr['xp'] = null;
				
				//Insérer les information sur la nouvelle compétence
				$this->comp[$compId] = array(
									'id' => $compId,
									'abbr' => $arr['abbr'],
									'nom' => stripslashes($arr['nom']),
									'xp' => $arr['xp'],
									'mod' => 0 //aucun niveau de modification
									);
				
				if($lastId != 0 && $totalMulti > 0)
				{
					//Effectuer la moyenne des bonus sur la compétence précédente
					$this->comp[$lastId]['mod'] /= $totalMulti;
				}
				
				$totalMulti = 0;
				$lastId = $compId;
			}
			
			
			//S'il y a des modificateurs de stats, les ajouter
			if(!empty($arr['statid']))
			{
				$statId = $arr['statid'];
				$compId = $arr['id'];
				
				//Calculer l'XP de la stat modifié par les drogues
				$statXp = $this->stat[$statId]['xp'] + abs($this->stat[$statId]['xp']) * $this->stat[$statId]['drug_multi'];
				
				//Calculé le level * le multiplicateur
				$lvl = self::convStatXpToLevel($statXp) * $arr['stat_multi'];
				
				//Incrémenter le total des multiplicateurs
				$totalMulti += $arr['stat_multi'];
				
				//Incrémenter le niveau de bonus (la moyenne sera fait une fois tout les niveau de la compétence trouvé)
				$this->comp[$compId]['mod'] += $lvl;
			}
			
		}
		
		//Effectuer la moyenne des bonus sur la dernière compétence
		if(isset($totalMulti) && $totalMulti>0)
			$this->comp[$lastId]['mod'] /= $totalMulti;
		
	}

	
	
	/** 
	 * Modifie les compétances.
	 *
	 * Exemple d'utilisation - Modifier les compétances du personnage actuel
	 * 
	 * <code>
	 * $perso->setComp(array('FRTV' => rand(1,3) ));
	 * </code>
	 *
	 * @param array Tableau avec 1 instruction par ligne. Code de comp en index, gain (d' XP) en valeur.
	 * @return string Message d'information pour que le joueur prenne connaissance de ses gains.
	 */
	public function setComp($arr)
	{
		$this->checkCompLoad();
		
		$keys = array_keys($arr);
		
		//Première boucle de validation
		foreach($keys as $key)
		{
			if (!is_numeric($arr[$key]))
			{
				throw new Exception('Format non respecté. Int attendu, ' . $arr[$key] . ' recu.');
				return false;
			}
		}
		
		//Générer le message
		$msg = '';
		foreach($keys as $key)
		{
			try
			{
				$compId = $this->convCompCodeToId($key);
			}
			catch(Exception $e)
			{
				throw $e;
				return false;
			}
			
			if($msg!='')
				$msg .= ', ';
			$msg .= $this->getCompName($compId) . '+' . $arr[$key];
		}
		
		
		
		//Sauvegarder les compétences
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		foreach($keys as $key)
		{
			$compId		= $this->convCompCodeToId($key);
			$xp			= $this->getCompXp($compId);
			
			if($xp===null)
			{
				$xp = $arr[$key];
				$query = 'INSERT INTO `' . DB_PREFIX . 'perso_competence`'
							. ' (persoid, compid, `xp`)'
							. ' VALUES'
							. ' (:persoId, :compId, :xp);';
			}
			else
			{
				$xp		+= $arr[$key];
				$query = 'UPDATE `' . DB_PREFIX . 'perso_competence`'
						. ' SET `xp`=:xp'
						. ' WHERE	compid=:compId'
							. ' AND persoid=:persoId'
						. ' LIMIT 1;';
			}
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId',	$this->id,	PDO::PARAM_INT);
			$prep->bindValue(':compId',		$compId,	PDO::PARAM_INT);
			$prep->bindValue(':xp',			$xp,		PDO::PARAM_INT);
			$prep->executePlus($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
		
		
		return $msg;
	}
	
	
	/** 
	 * Retourne le tableau des compétences
	 *
	 * @return array
	 */
	 public function getComp()
	 {
	 	$this->checkCompLoad();
	 	
	 	$arr = array();
	 	foreach($this->comp as $comp)
	 		$arr[] = $comp['id'];
	 	return $arr;
	 }
	 
	 
	/**
	 * Converti un taux d'XP en level. (pour les compétances)
	 *
	 * @param int $xp Taux d'XP à convertir
	 * @return int Niveau
	 */
	public static function convCompXpToLevel ( $xp )
	{
		$comp_lvlxp['0'] = 0;
		$comp_lvlxp['1'] = 25;
		$comp_lvlxp['2'] = 63;
		$comp_lvlxp['3'] = 119;
		$comp_lvlxp['4'] = 203;
		$comp_lvlxp['5'] = 330;
		$comp_lvlxp['6'] = 520;
		$comp_lvlxp['7'] = 804;
		$comp_lvlxp['8'] = 1231;
		$comp_lvlxp['9'] = 1872;
		$comp_lvlxp['10'] = 2872;
		$comp_lvlxp['11'] = 4275;
		$comp_lvlxp['12'] = 6437;
		
		for($i=0;$i<count($comp_lvlxp);$i++)
			if ($comp_lvlxp[$i]>$xp)
				return ($i-1);
		return count($comp_lvlxp)-1;
	}
	
	/** 
	 * Converti un level entaux d'XP. (pour les compétances)
	 *
	 * @param int $lvl Level à convertir convertir
	 * @return int Nombre d'XP
	 */
	public static function convCompLevelToXp ( $lvl ) 
	{
		if($lvl<0)
			$lvl=0;
		if($lvl>12)
			$lvl=12;
		
		switch($lvl)
		{
			case 0:		return 0;		break;
			case 1:		return 25;		break;
			case 2:		return 63;		break;
			case 3:		return 119;		break;
			case 4:		return 203;		break;
			case 5:		return 330;		break;
			case 6:		return 520;		break;
			case 7:		return 804;		break;
			case 8:		return 1231;	break;
			case 9:		return 1872;	break;
			case 10:	return 2872;	break;
			case 11:	return 4275;	break;
			case 12:	return 6437;	break;
		}
	}
	
	
	/** 
	 * Retourne l'id d'une Compétance.
	 * 
	 * @param string $code Code technique de la comp (Ex.: 'AFE' )
	 * @return string Id de la comp (Ex.: 4)
	 */
	public function convCompCodeToId($code)
	{
		$this->checkCompLoad();
		
		foreach($this->comp as $comp)
			if(strtolower($comp['abbr'])==strtolower($code))
				return $comp['id'];
				
		throw new Exception('Compétence innexistante (' . $code . ')');
	}
	
	
	/** 
	 * Retourne le nom affichable d'une Compétance.
	 * 
	 * @param string $id Id de la comp (Ex.: 4 )
	 * @return string Nom affichable de la comp (Ex.: 'Arme à feu')
	 */
	public function getCompName($id)
	{
		$this->checkCompLoad();
		
		if(isset($this->comp[$id]))
			return $this->comp[$id]['nom'];
		
		throw new Exception('Compétence innexistante (' . $id . ')');
	}
	
	
	/** 
	 * Retourne le code d'une Compétance.
	 * 
	 * @param string $id Id de la comp (Ex.: 4 )
	 * @return string Code de la comp (Ex.: 'armf')
	 */
	public function getCompCode($id)
	{
		$this->checkCompLoad();
		
		if(isset($this->comp[$id]))
			return strtolower($this->comp[$id]['abbr']);
		
		throw new Exception('Compétence innexistante (' . $id . ')');
	}
	
	
	/**
	 * Retourne l'XP (non modifié) d'une compétance.
	 *
	 * @param string $id Id de la comp (Ex.: 4 )
	 * @return int Niveau d'XP d'une compétance
	 */
	public function getCompXp($id)
	{
		$this->checkCompLoad();
		
		if(isset($this->comp[$id]))
			return $this->comp[$id]['xp'];
			
		throw new Exception('Compétence innexistante (' . $id . ')');
	}

	
	/** 
	 * Retourne le niveau non-modifié d'une compétance.
	 * 
	 * @param string $id Id de la comp (Ex.: 4 )
	 * @return int Niveau réel d'une compétance
	 */
	public function getCompRawLevel($id)
	{
		$this->checkCompLoad();
		
		try
		{
			$xp = $this->getCompXp($id);
			$lvl = self::convCompXpToLevel($xp);
		}
		catch(Exception $e)
		{
			throw $e;
			$lvl = 0;
		}
		
		return $lvl;
	}
	
	
	/** 
	 * Retourne le niveau modifié (par les stats) d'une compétance.
	 * 
	 * @param string $id Id de la comp (Ex.: 4 )
	 * @return int Niveau réel d'une compétance
	 */
	public function getCompRealLevel($id)
	{
		$this->checkCompLoad();
		
		try
		{
			$lvl = $this->getCompRawLevel($id);
			$lvl += $this->comp[$id]['mod'];
			$lvl = round($lvl);
			if($lvl<=0) //plus grand ou ÉGAL: Évite les -0 du à l'arrondissement
				$lvl=0;
			if($lvl>12)
				$lvl=12;
		}
		catch(Exception $e)
		{
			throw $e;
			$lvl = 0;
		}
		
		return $lvl;
	}

	
	/** 
	 * Retourne les chances de réussite du personnage dans une compétance particulière
	 * 
	 * Note: Cette fonction prend en compte les modification des STATS et des DROGUES
	 *
	 * @param string $code Code technique de la comp (Ex.: 'AFE' )
	 * @return int Pourcentage de réussite dans cette compétance
	 */
	public function getChancesReussite($code)
	{
		$this->checkCompLoad();
		
		try
		{
			$id = $this->convCompCodeToId($code);
			$lvl = $this->getCompRealLevel($id);
			return self::convLevelToChance($lvl);
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}
	
	
	/** 
	 * Converti un niveau (level) en pourcentage de chance de réussite
	 *
	 * Exemple d'utilisation
	 * <code>
	 * echo "Chances de réussite (sur 100): " . Member_Perso::convLevelToChance(5);
	 * </code>
	 * 
	 * @param int $level Niveau de la compétance
	 * @return int Pourcentage de réussite de ce niveau
	 */
	private static function convLevelToChance($level)
	{
		$comp_lvl = array(	0 => 15,
							1 => 25,
							2 => 30,
							3 => 36,
							4 => 43,
							5 => 51,
							6 => 60,
							7 => 70,
							8 => 80,
							9 => 85,
							10 => 90,
							11 => 93,
							12 => 95
						);
		
		return $comp_lvl[$level];
	}

	
	/** 
	 * Retourne le nombre de message dans le HE.
	 *
	 * Exemple d'utilisation
	 * <code>
	 * echo "Message dans le HE: " . $perso->getHeMsgCount();
	 * </code>
	 * 
	 * @return int Nombre de message dans le HE
	 */
	public function getHeMsgCount()
	{
		return $this->heQte;
	}


	/** 
	 * Retourne le statut du Visa Vert
	 *
	 * Exemple d'utilisation
	 * <code>
	 * echo "Visa vert réussit: " . ($perso->getVisaPerm()=='1' ? 'oui' : 'non');
	 * </code>
	 * 
	 * @return string "0"=Non, "1"=Oui, autre=# dernier examen
	 */
	public function getVisaPerm()
	{
		return $this->visaPerm;
	}
}

