<?php
/** 
 * Gestion des lieux. 
 * 
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.1
 * @package Member
 * @subpackage Lieu
 */
class Member_Lieu
{
	private $dimension;
	private $image;
	
	/**
	 * @var int
	 * @access private
	 */
	private $gerantids;
	
	/**#@+
	 * @var array
	 * @access private
	 */
	/**
	 * Lien vers d'autres lieux
	 * @see Member_LieuLien
	 */
	private $liens;
	
	/**
	 * Items sur le sol du lieu
	 * @see Member_Item
	 */
	private $items;
	
	/**
	 * Casiers dans le lieu
	 * @see Member_Casier
	 */
	private $casiers;
	
	/**
	 * Personnages présents dans le lieu
	 * @see Member_Perso
	 */
	private $persoList;
	
	/** 
	 * Items en inventaire dans la boutique
	 * @see Member_Item
	 */
	private $boutiqueInventaire;
	/**#@-*/
	
	
	
	/**
	 * Si le lieu est une boutique
	 * @var bool;
	 * @access protected
	 */
	private $boutique;
	
	/** Solde de la caisse de la boutique
	* @var float
	* @access private
	*/
	private $boutiqueCash;
	
	/** Numéro complet du compte vers lequel les paiements direct sont envoyés.
	* @var string
	* @access private
	*/
	private $boutiqueCompte;
	
	/** Si la boutique permet le vol
	* @var bool
	* @access private
	*/
	private $boutiqueVol;
	
	
	/** Id du lieu.
	* @var int
	* @access protected
	*/
	protected $id;
	
	/** Nom technique du lieu.
	* @var string
	* @access protected
	*/
	protected $nomTech;
	
	/** Nom affichable du lieu.
	* @var string
	* @access protected
	*/
	protected $nom;
	
	/** Description affichable du lieu.
	* @var string
	* @access protected
	*/
	protected $description;
	
	/** Coefficient de soin du lieu.
	* @var int
	* @access protected
	*/
	protected $coeffSoin;
	
	/** Quantité de matériel médical dans le lieu
	 * @var int
	 * @access protected
	 */
	protected $qteMateriel;
	
	/*
	 * Accès média dans le lieu
	 * @see Member_MediaAcces
	 */
	private $mediaAcces;
	
	
	/** 
	 * Charge un lieu en mémoire.
	 * 
	 * Utiliser la méthode Member_LieuFactory si possible.
	 * 
	 *
	 * @param string $lieuTech Nom technique du lieu à charger
	 */ 
	function __construct(&$arr)
	{
		
		$this->id 			= $arr['id'];
		$this->nom 			= stripslashes($arr['nom_affiche']);
		$this->nomTech 		= $arr['nom_technique'];
		$this->description	= stripslashes($arr['description']);
		$this->dimension	= $arr['dimension'];
		$this->image		= $arr['image'];
		$this->boutique 	= false;
		$this->boutiqueCash = $arr['boutique_cash'];
		$this->boutiqueCompte	= $arr['boutique_compte'];
		$this->boutiqueVol	= $arr['boutique_vol'] != 0 ? true : false;
		$this->coeffSoin	= $arr['coeff_soin'];
		$this->qteMateriel	= $arr['qteMateriel'];
		$this->persoList	= array();
		$this->liens		= array();
		$this->items		= array();
		$this->gerantids	= array();
	}


	
	
	/**
	 * Charge la liste des liens du lieu.
	 * 
	 * Selon le lieu actuel (de l'objet lieu), cette fonction créer/génère
	 * le 'tableau des déplacements'. Chaque lien (ou déplacement) est en
	 * soit un lieu. 
	 * 
	 * Cette fonction est apellée automatiquement par getLink() dans le cas
	 * ou le 'tableau des déplacements' est innexistant.
	 *
	 * @todo: Si plusieurs perso sont banni d'un lieu, plusieurs liens identique seront créé. Il faut faire un sous-tableau.
	 * @see getLink()
	 * @param int $persoid Utile pour déterminer si un perso est banni d'accès au lieu.
	 */ 
	private function loadLinks($persoid=0)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		//Charger les informations générale du lien
		$query = 'SELECT ll.*, l.`nom_affiche`, l.`description`, l.`id` as lid, b.id as banid'
				. ' FROM ' . DB_PREFIX . 'lieu_lien as ll'
				. ' INNER JOIN ' . DB_PREFIX . 'lieu as l ON (l.`nom_technique`=ll.`to`)'
				. ' LEFT JOIN ' . DB_PREFIX . 'lieu_ban as b ON (b.lieu=l.`nom_technique` AND b.persoid=:persoId)'
				. ' WHERE `from`=:nomTech;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$persoid,		PDO::PARAM_INT);
		$prep->bindValue(':nomTech',	$this->nomTech,	PDO::PARAM_STR);
		$prep->execute($db, __FILE__,__LINE__);
		$arrLien = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		foreach($arrLien as $arr)
		{
			if($arr['lid']=='')
				fctBugReport('Un lien existe, mais pas le lieu', array($query, $arr), __FILE__, __LINE__, __FUNCTION__, __CLASS__, __METHOD__, true, false, false);
			
			$this->liens[] = new Member_LieuLien($arr);
		}
	}
	
	
	
	/** 
	 * Retourne un lien possible du 'tableau des déplacements'.
	 * 
	 * Selon le lieu actuel (de l'objet lieu), cette fonction
	 * retournera un lien du 'tableau des déplacements'. Chaque
	 * lien (ou déplacement) est en soit un lieu. Lorsque l'indexe
	 * demandé n'existe pas, la fonction retourne FALSE.
	 *
	 * Exemple d'utilisation:
	 * <code>
	 * $i=0;
	 * while($lien = $lieu->getLink($i++))
	 * 	echo $lien->to;
	 * </code>
	 *
	 * @param int $id Indexe du lien (dans le tableau)  à retourner
	 * @param int $persoid Utile pour déterminer si un perso est banni d'accès au lieu.
	 * @return Member_LieuLien
	 */ 
	public function getLink($id, $persoid=0)
	{
		//Vérifier si les liens sont chargés
		if (empty($this->liens))
			$this->loadLinks($persoid);
			
		//Vérifier si le lien demandé est hors des liens existant
		if (!is_numeric($id) || $id>=count($this->liens))
			return false;
		
		//Retourner le lien demandé
		return $this->liens[$id];
	}
	
	
	
	/**
	 * Valider si un lien est existant.
	 * 
	 * Selon le lieu actuel (de l'objet lieu), cette fonction
	 * valide si le déplacement est possible vers le lieu passé
	 * en paramètre
	 * 
	 * Exemple d'utilisation - Valider si le déplacement vers A.biblio est possible.
	 * <code>
	 * if (!$lieu->confirmLink('A.biblio'))
	 * 	die ('Ce déplacement ne vous est pas authorisé');
	 * </code>
	 *
	 * @param string $to Nom technique du lieu de destination
	 * @return boolean
	 */
	public function confirmLink($to)
	{
		$i=0;
		while( $tmp = $this->getLink($i++))
			if ($tmp->getTo() == $to)
				return true;
				
		return false;
	}
	
	
	

	
	
	
	/**
	 * Fonction qui permet de convertir un nom technique en nom affichable.
	 *
	 * Fonctionne pour tout les lieux, pas uniquement le lieu actuel.
	 * 
	 * Exemple d'utilisation:
	 * <code>
	 * echo "Vous êtes dans le lieu: " . $lieu->convertNomTech('A.biblio');
	 * </code>
	 *
	 * @param string $nomTech Nom technique du lieu à convertir
	 * @return string
	 */
	public static function convertNomTech($nomTech)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'SELECT nom_affiche'
				. ' FROM ' . DB_PREFIX . 'lieu'
				. ' WHERE nom_technique=:nomTech'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':nomTech',	$nomTech,	PDO::PARAM_STR);
		$prep->execute($db, __FILE__,__LINE__);
		return $prep->fetch();
	}
	
	
	
	
	
	
	
	
	/**
	 * Charge la liste des personnages du lieu.
	 *
	 * Selon le lieu actuel (de l'objet lieu), cette fonction créer/génère
	 * le 'tableau des personnages'. Cette fonction est apellée par {@link getPerso()}
	 * dans le cas ou le 'tableau des perso' est innexistant.
	 *
	 * @see getPerso()
	 * @param object &$perso Personnage actuel à exclure (pour le pas l'avoir dans la liste)
	 */ 
	private function refreshPersoList(&$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$this->persoList[$perso->getId()] = array();
		
		$query = 'SELECT p.*, pc.nom as nom'
				. ' FROM ' . DB_PREFIX . 'perso as p'
				. ' LEFT JOIN '.DB_PREFIX.'perso_connu AS pc ON ( pc.persoid =:persoId AND pc.nomid = p.id )'
				. ' WHERE lieu=:nomTech;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$perso->getId(),	PDO::PARAM_INT);
		$prep->bindValue(':nomTech',	$this->nomTech,		PDO::PARAM_STR);
		$prep->execute($db, __FILE__,__LINE__);
		$arrPersoConnu = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		
		foreach($arrPersoConnu as $arr)
		{
			//Ne pas marquer inconnu pour notre propre perso
			if ($arr['id'] == $perso->getId())
				$arr['nom'] = $perso->getNom();
			
			$this->persoList[$perso->getId()][] = new Member_Perso($arr); //Sans le lieu et l'inventaire
		}
	}
	
	/**
	 * Personnages présent dans le lieu.
	 *
	 * Fonctionne pour le lieu actuel. Retournera les informations de
	 * base uniquement. (pas de lieu, pas d'inventaire)
	 *
	 * Exemple d'utilisation - Charger la liste des joueurs d'un lieu
	 * <code>
	 * $i=0; $e=0;
	 * while( $tmp = $perso->getLieu()->getPerso($perso, $i++))
	 * 	$persoDansLeLieuActuel[$e++] = $tmp;
	 * </code>
	 * 
	 * Exemple d'utilisation - Afficher une liste des personnage du lieu actuel
	 * <code>
	 * $i=0;
	 * while( $arrPerso = $perso->getLieu()->getPerso($perso, $i++)) 
	 * 	echo '<br /> - ' . $arrPerso->getNom();
	 * </code>
	 * 
	 * @see Member_Perso
	 * @param Member_Perso &$perso Personnage sur lequel se baser dans le cas ou la liste de personnage doit-être générée (pour savoir qui est connu et inconnu)
	 * @param int $id Indexe du perso (dans le tableau) à retourner
	 * @return Member_Perso|false
	 */
	public function getPerso(&$perso, $id)
	{
	
		if(!$perso instanceof Member_Perso)
			die('Perso doit être un objet de la classe Member_Perso: <hr />' . var_export($perso, true));
		
		//Vérifier si les perso sont chargés
		if (empty($this->persoList) || !isset($this->persoList[$perso->getId()]) || empty($this->persoList[$perso->getId()]))
			$this->refreshPersoList($perso);
			
		//Vérifier si l'index de personnage demandé est hors de la liste existante
		if (!is_numeric($id) || $id>=count($this->persoList[$perso->getId()]))
			return false;
		
		return $this->persoList[$perso->getId()][$id];
	}
	

	
	/**
	 * Fonction qui valide si un perso est présent dans le lieu
	 *
	 * @param Member_Perso &$perso Personnage sur lequel se baser dans le cas ou la liste de personnage doit-être générée (pour savoir qui est connu et inconnu)
	 * @param int $id Indexe du perso à vérifier
	 * @return bool
	 */
	public function confirmPerso(&$perso, $id)
	{
		$i=0;
		while( $tmp = $this->getPerso($perso, $i++))
			if($tmp->getId() === $id)
				return true;
				
		return false;
	}
	
	
	
	
	
	
	/**
	 * Charge la liste des items en inventaire.
	 *
	 * Selon le lieu actuel (de l'objet lieu), cette fonction créer/génère
	 * le 'tableau des item en inventaire'.
	 * Cette fonction est apellée par {@link getItems()}
	 *
	 * @see getItems()
	 */ 
	private function refreshItems()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Vider l'inventaire
		$this->items = NULL;
		
		//Charger le nouvel inventaire
		$this->items = Member_ItemFactory::createFromNomTechLieu($this->getNomTech());
		
	}
	

	
	
	/**
	 * Charge l'inventaire du lieu.
	 * 
	 * Fonctionne pour le lieu actuel. Retournera les informations
	 * sur les items.
	 *
	 * Exemple d'utilisation - Afficher des items de type badge
	 * <code>
	 * $i=0; $e=0;
	 * while( $item = $lieu->getItems($i++))
	 *	if($item instanceof Member_ItemBadge)
	 *		$badgeDansLeLieu[$e++] = $item;
	 * </code>
	 * 
	 * @param int $id Indexe de l'item en inventaire(dans le tableau) à retourner
	 * @return Member_Item|mixed|false Selon le type d'item
	 */
	public function getItems($id)
	{
		//Vérifier si les items sont chargés
		if (empty($this->items))
			$this->refreshItems();
			
		//Vérifier si l'index de l'item demandé est hors de la liste existante
		if (!is_numeric($id) || $id>=count($this->items))
			return false;
		
		return $this->items[$id];
	}
	
	
	
	
	/**
	 * Charge la liste des casiers dans le lieu.
	 *
	 * Selon le lieu actuel (de l'objet lieu), cette fonction
	 * créer/génère le 'tableau des casiers existants'.
	 * Cette fonction est apellée par {@link getCasiers()}
	 *
	 * @see getCasiers()
	 */ 
	private function refreshCasiers()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Vider la liste de casiers
		$this->casiers = NULL;
		
		//Lister la nouvelle liste de casiers
		$query = 'SELECT *'
				. ' FROM '.DB_PREFIX.'lieu_casier'
				. ' WHERE lieuId=:id'
				. ' ORDER BY id_casier;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',		$this->getId(),		PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arrCasier = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		foreach($arrCasier as $arr)
			$this->casiers[] = new Member_Casier($arr);
		
	}
	
	
	/**
	 * Charge un casier du lieu.
	 * 
	 * Fonctionne pour le lieu actuel. Retournera l'objet du casier demandé.
	 *
	 * Exemple d'utilisation - Afficher le nom du casier 345
	 * <code>
	 * $i=0;
	 * while( $casier = $lieu->getCasiers($i++))
	 *	if($casier->getId()==345)
	 *		echo "Casier de : " . $casier->getNom();
	 * </code>
	 * 
	 * @param int $id		Indexe du casier (dans le tableau) à retourner.
	 * @return Member_Casier|false
	 */
	public function getCasiers($id)
	{
		
		//Vérifier si les items sont chargés 
		if (empty($this->casiers))
			$this->refreshCasiers();
		
		//Vérifier si l'index de l'item demandé est hors de la liste existante
		if (!is_numeric($id) || $id>=count($this->casiers))
			return false;
		
		//echo "..." . count($this->casiers);
		return $this->casiers[$id];
	}
	
	
	
	
	/**
	 * Charge la liste des items en inventaire dans la boutique.
	 * 
	 * Selon le lieu actuel (de l'objet lieu), cette fonction créer/génère
	 * le 'tableau des item en inventaire'.
	 * Cette fonction est apellée par {@link getBoutiqueInventaire()}
	 *
	 * @see getBoutiqueInventaire()
	 */ 
	private function refreshBoutiqueItems()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		//Vider l'inventaire
		$this->boutiqueInventaire = NULL;
		
		//Charger le nouvel inventaire
		$this->boutiqueInventaire = Member_ItemFactory::createFromNomTechBoutique($this->getNomTech());
		
	}
	

	
	
	/**
	 * Charge l'inventaire de la boutique du lieu.
	 * 
	 * Fonctionne pour le lieu actuel. Retournera les informations
	 * sur les items de l'inventaire de la boutique.
	 *
	 * Exemple d'utilisation - Afficher des items de type badge
	 * <code>
	 * $i=0; $e=0;
	 * while( $item = $lieu->getBoutiqueInventaire($i++))
	 *	if($item instanceof Member_ItemBadge)
	 *		$badgeDansLeLieu[$e++] = $item;
	 * </code>
	 * 
	 * @param int $id Indexe de l'item en inventaire(dans le tableau) à retourner
	 * @return Member_Item|mixed|false Selon le type d'item
	 */
	public function getBoutiqueInventaire($id)
	{ 
		//Vérifier si les items sont chargés
		if (empty($this->boutiqueInventaire))
			$this->refreshBoutiqueItems();
			
		//Vérifier si l'index de l'item demandé est hors de la liste existante
		if (!is_numeric($id) || $id>=count($this->boutiqueInventaire))
			return false;
		
		return $this->boutiqueInventaire[$id];
	}
	
	
	
	
	/** 
	 * Modifier le montant de la caisse de la boutique.
	 *
	 * Cette méthode s'occupe de calculer elle même le total.
	 * Vous DEVEZ appeler {@link setBoutiqueCash()} pour sauvegarder
	 * les changements.
	 * 
	 * @see setBoutiqueCash()
	 * @param char $plusMoins Prend la valeur '+' ou '-' selon si on désire ajouter ou retirer de l'argent
	 * @param float $montant Montant de Cash à ajouter ou retirer
	 */
	public function changeBoutiqueCash($plusMoins, $montant)
	{
		$montant = floor($montant);
		
		if($plusMoins=='+')
			$this->boutiqueCash+=$montant;
		elseif($plusMoins=='-')
			$this->boutiqueCash-=$montant;

		if($this->boutiqueCash<0)
			$this->boutiqueCash=0;
	}
	
	/**
	 * Modifier directement le montant de la caisse de la boutique.
	 *
	 * Cette méthode modifie directement le solde de la caisseé.
	 * NOTE: La méthode {@link changeBoutiqueCash()} permet d'effectuer
	 * automatiquement les calculs de solde.
	 *
	 * @see changeBoutiqueCash()
	 * @param float $montant Montant auquel placer le solde du compte
	 */
	public function setBoutiqueCash($montant=null)
	{
	
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante

		if(!empty($montant))
			$this->boutiqueCash = $montant;
		
		$query = 'UPDATE ' . DB_PREFIX . 'lieu'
					. ' SET boutique_cash=:cash'
					. ' WHERE id=:id'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':cash',	$this->boutiqueCash,	PDO::PARAM_INT);
		$prep->bindValue(':id',		$this->id,				PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
	
	
	
	/**
	 * Retourne l'ID du lieu
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * Retourne le nom technique du lieu.
	 *
	 * @return string
	 */
	public function getNomTech()
	{
		return $this->nomTech;
	}
	
	/**
	 * Retourne le nom du lieu.
	 *
	 * @return string
	 */
	public function getNom()
	{
		return $this->nom;
	}
	
	/**
	 * Retourne la description du lieu.
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	} 
	
	/**
	 * Retourne la dimension technique du lieu.
	 *
	 * @return string
	 */
	public function getDimension()
	{
		return $this->dimension;
	} 
	
	/**
	 * Retourne l'URL de l'image du lieu.
	 *
	 * @return string
	 */
	public function getImage()
	{
		return $this->image;
	} 
	
	/**
	 * Valide si un perso est gérant d'une boutique
	 *
	 * @param $perso perso à vérifier
	 * @return bool
	 */
	public function isGerant($perso)
	{
		//Vérification si $gerantids est initialisé
		if(empty($gerantids))
			$this->refreshGerantids();
			
		foreach($this->gerantids as $id)
			if($perso->getId() == $id)
				return true;
		return false;
	} 
	
	/**
	 * Fonction qui rempli la liste des ids des gérants de la boutique
	 */
	private function refreshGerantids()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$this->gerantids = array();
		
		$query = 'SELECT `persoid` FROM `' . DB_PREFIX . 'boutiques_gerants`'
				. ' WHERE `boutiqueid` = :lieuId';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$this->id, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arrGerants = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		if(!empty($arrGerants))
			foreach($arrGerants as $arr)
				$this->gerantids[] = $arr['persoid'];
	}
	
	/**
	 * Valide si le lieu est une boutique.
	 *
	 * @return bool
	 */
	public function isBoutique()
	{
		if(!$this->boutique)
		{
			if(empty($this->gerantids))
			{
				$this->refreshGerantids();
			}
			$this->boutique = (empty($this->gerantids))? false : true;
		}
		return $this->boutique;
	} 
	
	/**
	 * Si le lieu (qui doit être une boutique) permet le vol
	 *
	 * @return bool
	 */
	public function canVol()
	{
		return $this->boutiqueVol;
	} 
	
	/** 
	 * Valide si le lieu est un laboratoire de drogue.
	 *
	 * @return bool
	 */
	public function isLaboDrogue()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'SELECT id'
				. ' FROM ' . DB_PREFIX . 'lieu_menu'
				. ' WHERE 	url="Labodrogue"'
					. ' AND	lieutech=:nomTech'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':nomTech',	$this->getNomTech(),	PDO::PARAM_STR);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		return ($arr !== false);
	} 
	
	
	/**
	 * Retourne le solde de la caise de la boutique.
	 *
	 * @return float
	 */
	public function getBoutiqueCash()
	{
		return $this->boutiqueCash;
	} 
	

	/**
	 * Retourne le numéro de compte au format XXXX-XXXX-XXXX
	 *
	 * @return string
	 */
	public function getBoutiqueNoCompte()
	{
		return substr($this->boutiqueCompte,5,14);
	}
	
	/**
	 * Retourne le numéro de banque au format XXXX.
	 *
	 * @return string
	 */
	public function getBoutiqueNoBanque()
	{
		return substr($this->boutiqueCompte,0,4);
	}
	
	/**
	 * Retourne le coefficient de soin du lieu.
	 *
	 * Légende:
	 * - 0 = Soin de blessés légés (le personnage est autonome)
	 * - 1 = Soin des non-autonomes (le personnage est conscient)
	 * - 2 = Soin des inconscients (le personnage est vivant)
	 * - 3 = Ré-animation possible (le personnage est mort)
	 * @return int
	 */
	public function getCoeffSoin()
	{
		return $this->coeffSoin;
	}
	
	/**
	* Retourne la quantité de matériel médical du lieu.
	*
	* @return int
	*/
	public function getQteMateriel()
	{
		return $this->qteMateriel;
	}
	
	/**
	 * Charge la liste des accès médias dans le lieu.
	 *
	 * Selon le lieu actuel (de l'objet lieu), cette fonction
	 * créer/génère le 'tableau des accès médias existants'.
	 * Cette fonction est apellée par {@link getMediaAcces()}
	 *
	 * @see getMediaAcces()
	 */ 
	private function refreshMediaAcces()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Vider la liste de casiers
		$this->mediaAcces = NULL;
		
		//Lister la nouvelle liste de casiers
		$query = 'SELECT *'
				. ' FROM '.DB_PREFIX.'lieu_medias'
				. ' WHERE lieuId=:id'
				. ' ORDER BY id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',		$this->getId(),		PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arrMediaAcces = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		foreach($arrMediaAcces as $arr)
			$this->mediaAcces[] = new Member_MediaAcces($arr);
		
	}
	
	/**
	 * Charge un accès média du lieu.
	 * 
	 * Fonctionne pour le lieu actuel. Retournera l'objet de l'accès média demandé.
	 *
	 * Exemple d'utilisation - Afficher le nom de l'accès média 345
	 * <code>
	 * $i=0;
	 * while( $media = $lieu->getMediaAcces($i++))
	 *	if($media->getId()==345)
	 *		echo $media->getNom();
	 * </code>
	 * 
	 * @param int $id		Indexe de l'accès média (dans le tableau) à retourner.
	 * @return Member_MediaAcces|false
	 */
	public function getMediaAcces($id)
	{
		
		//Vérifier si les items sont chargés 
		if (empty($this->mediaAcces))
			$this->refreshMediaAcces();
		
		//Vérifier si l'index de l'item demandé est hors de la liste existante
		if (!is_numeric($id) || $id>=count($this->mediaAcces))
			return false;
		
		//echo "..." . count($this->casiers);
		return $this->mediaAcces[$id];
	}
	
	/**
	 * Supprime un accès média du lieu
	 * 
	 * Fonctionne pour le lieu actuel.
	 * 
	 * @param Member_MediaAcces $media	Accès média à supprimer
	 * @return true|false 				Si l'accès média existe ou pas
	 */
	 public function delMediaAcces($media)
	 {
		if(!($media instanceof Member_MediaAcces))
			return false;
		$id = $media->getId();
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'DELETE'
				. ' FROM '.DB_PREFIX.'lieu_medias'
				. ' WHERE `Id`=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$id, PDO::PARAM_INT);
		$count = $prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Si la requete supprimer rien
		if($count == 0)
			return false;
		
		return true;
	 }
	 
	 /**
	  * Ajoute un accès média au lieu
	  * 
	  * Fonctionne pour le lieu actuel.
	  * 
	  * @param string 	$name				nom de l'accès
	  * @param string 	$mediaType			type de média accessible
	  * @param int 		$channelId			canal sur lequel l'accès est branché
	  * @param int 		$interactionType	sens de communication avec le média
	  * @return 		Member_MediaAcces	retourne l'accès média créé
	  */
	 public function addMediaAcces($name, $mediaType, $channelId, $interactionType)
	 {
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'INSERT INTO `'.DB_PREFIX.'lieu_medias`'
				. ' (`id`, `lieuid`, `nom`, `mediaType`, `canalId`, `interactionType`)'
				. ' VALUES'
				. ' (NULL, :lieuId, :nom, :mediaType, :canalId, :interactionType);';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',				$this->id, 			PDO::PARAM_INT);
		$prep->bindValue(':nom',				$name, 				PDO::PARAM_STR);
		$prep->bindValue(':mediaType',			$mediaType, 		PDO::PARAM_STR);
		$prep->bindValue(':canalId',			$channelId, 		PDO::PARAM_INT);
		$prep->bindValue(':interactionType',	$interactionType,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		//Trouver l'id
		$query = "SELECT LAST_INSERT_ID()";
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__,__LINE__);
		$result = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		$id = $result[0];
		
		$query = 'SELECT * FROM `'.DB_PREFIX.'lieu_medias`'
				. ' WHERE `id` = :id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id', $id, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		return new Member_MediaAcces($arr);
	 }
	 
	 /**
	  * Récupère l'historique des transactions de la boutique du lieu.
	  * L'historique est sous forme de tableau de transactions. Chaque transaction
	  * est représentée par son "id" et son "details".
	  * 
	  * Fonctionne pour le lieu actuel.
	  * 
	  * @return 		mixed|null	retourne l'historique ou null si aucun historique
	  */
	 public function getBoutiqueHistorique()
	 {
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$transactionType = array(	'ACH' => 'achat',
									'VEN' => 'vente',
									'DEP' => 'depot',
									'RET' => 'retrait');
									
		$query = 'SELECT * FROM `' . DB_PREFIX . 'boutiques_historiques`'
				. ' WHERE `boutiqueid` = :boutiqueid'
				. ' ORDER BY `date` DESC;';
		$prep = $db->prepare($query);
		$prep->bindValue(':boutiqueid', $this->id, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		if(count($arrAll) != 0)
		{
			$result = array();
			$i = 0;
			foreach($arrAll as $arr)
			{
				$result[$i]['id'] = $arr['id'];
				$result[$i]['details'] = fctToGameTime($arr['date'], true) . ' ' . $transactionType[$arr['transactiontype']] . ' de ';
				if($arr['transactiontype'] == 'ACH' || $arr['transactiontype'] == 'VEN')
				{
					$result[$i]['details'] .= $arr['itemlist'];
					if($arr['marchandage'])
						$result[$i]['details'] .= ' avec un rabais';
					else
						$result[$i]['details'] .= ' à prix régulier';
					$result[$i]['details'] .= ' : ';
					if($arr['moyenpaiement'])
						$result[$i]['details'] .= ' par carte bancaire ';
					else
						$result[$i]['details'] .= ' comptant ';
					$result[$i]['details'] .= ' : ';
				}
				$result[$i]['details'] .= $arr['prixtotal'] . ' ' . GAME_DEVISE . '.';
				
				$result[$i]['details'] = BBCodes($result[$i]['details']);
				$i++;
			}
			return $result;
		}
		
		return null;
	 }
	 
	 /**
	  * Ajoute une transaction à l'historique de la boutique
	  * 
	  * Fonctionne pour le lieu actuel.
	  * 
	  * @param 	string	$transactionType	Type de transaction : achat, vente, depot, retrait
	  * @param	int		$prixTotal			Montant total de la transaction
	  * @param	string	$itemList			Détail sur ce à quoi correspond le montant, généralement la liste des items vendus ou achetés (utile seulement pour l'achat et la vente)
	  * @param	bool	$marchandage		rabais ou non lors de la transaction (utile seulement pour l'achat et la vente)
	  * @param	int		$moyenPaiement		Moyen de paiement utilisé : CB = 1, comptant = 0 (utile seulement pour l'achat et la vente)
	  *
	  */
	 public function addBoutiqueHistorique($transactionType, $prixTotal, $itemList = null, $marchandage = null, $moyenPaiement = null)
	 {
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		//Vérifier le nombre de transactions
		$maxTransaction = 100;
		$historique = $this->getBoutiqueHistorique();
		if(count($historique) >= $maxTransaction)
		{
			//Id de la transaction la plus vieille
			$query = 'SELECT * FROM `' . DB_PREFIX . 'boutiques_historiques`'
					. ' WHERE `boutiqueid` = :boutiqueid'
					. ' ORDER BY `date` ASC LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':boutiqueid', $this->id, PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$arr = $prep->fetch();
			$prep->closeCursor();
			$prep = NULL;
			
			$transactionId = $arr['id'];
		
			//Suppression de la transaction la plus vieille
			$query = 'DELETE FROM `' . DB_PREFIX . 'boutiques_historiques`'
					. ' WHERE `id` = :id LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':id', $transactionId, PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
		
		//Ajouter la nouvelle transaction
		$transactionTypeConvertion = array(	'achat' 	=> 'ACH',
											'vente' 	=> 'VEN',
											'depot' 	=> 'DEP',
											'retrait' 	=> 'RET');
									
		$query = 'INSERT INTO `' . DB_PREFIX . 'boutiques_historiques`'
				. ' (`boutiqueid`, `date`, `transactiontype`, `itemlist`, `marchandage`, `moyenpaiement`, `prixtotal`)'
				. ' VALUES'
				. ' (:boutiqueid, UNIX_TIMESTAMP(), :transactiontype, :itemlist, :marchandage, :moyenpaiement, :prixtotal);';
		$prep = $db->prepare($query);
		$prep->bindValue(':boutiqueid', $this->id, PDO::PARAM_INT);
		$prep->bindValue(':transactiontype', $transactionTypeConvertion[$transactionType], PDO::PARAM_STR);
		$prep->bindValue(':itemlist', $itemList, PDO::PARAM_STR);
		$prep->bindValue(':marchandage', $marchandage, PDO::PARAM_INT);
		$prep->bindValue(':moyenpaiement', $moyenPaiement, PDO::PARAM_INT);
		$prep->bindValue(':prixtotal', $prixTotal, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
	 }
	 
	 /**
	  * Supprime toutes les transactions de l'historique de la boutique
	  * 
	  * Fonctionne pour le lieu actuel.
	  *
	  */
	 public function supprimerBoutiqueHistorique()
	 {
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'DELETE FROM `' . DB_PREFIX . 'boutiques_historiques`'
				. ' WHERE `boutiqueid` = :boutiqueid;';
		$prep = $db->prepare($query);
		$prep->bindValue(':boutiqueid', $this->id, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
	 }
}




