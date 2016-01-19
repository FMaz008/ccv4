<?php
/** Gestion de base appliquable à tout les items du jeu.
 *
 * @package Member
 * @subpackage Item
 */
abstract class Member_Item
{

	/** Id dans la table inventaire.
	 * @var string
	 * @access protected
	 */
	protected $invId;

	/** Id du catalogue des items.
	 * @var string
	 * @access protected
	 */
	protected $dbId;

	/** Id du propriétaire de l'item.
	 * @var string
	 * @access protected
	 */
	protected $idProprio;	
	
	/** Lieu de l'item (au sol).
	 * @var string
	 * @access protected
	 */
	protected $lieuItem;
	
	/** Lieu de l'item (boutique).
	 * @var string
	 * @access protected
	 */
	protected $boutiqueItem;	

	/** Si l'item supporte d'être regroupé.
	 * @var bool
	 * @access protected
	 */
	protected $regrouper;
	
	/** Nom affichable de l'item.
	 * @var string
	 * @access protected
	 */
	protected $nom;
	
	/** Description de l'item.
	 * @var string
	 * @access protected
	 */
	protected $description;
	
	/** Nom du fichier image représentant l'item.
	 * @var string
	 * @access protected
	 */
	protected $img;
	
	/** Espace physique qu'occupe l'item dans un inventaire.
	 * @var int
	 * @access protected
	 */
	protected $pr;
	
	/** Note cachée aux joueurs qu'un MJ peut laisser à propo de ce type d'item en général.
	 * @var string
	 * @access protected
	 */
	protected $noteMjDb;
	
	/** Note cachée aux joueurs qu'un MJ  peut laisser à propo de cet item précis.
	 * @var string
	 * @access protected
	 */
	protected $noteMjItem;
	
	/** Quantité de l'item. 
	 * <br> (Pour les items ne possédant pas de gestion de lot ou de possibilité de groupement, cette valeur est à 1 par défaut)
	 * @var string
	 * @access protected
	 */
	protected $qte;
	
	/** Statut d'équipement de l'item.
	 * <br> Si l'item est équipée sur le personnage, cette variable sera à 1, sinon à 0.
	 * @var int
	 * @access protected
	 */
	protected $equip;
	
	/**  Prix de l'item en vente dans la boutique
	 * @var float
	 * @access protected
	 */
	protected $boutiquePrixVente;
	
	/** Prix offert pour l'achat de cet item dans la boutique
	 * @var float
	 * @access protected
	 */
	protected $boutiquePrixAchat;
	
	/** Numero de cachette
	 * @var int
	 * @access protected
	 */
	protected $cacheNo;
	
	/** Taux de réussite de la cache
	 * @var int
	 * @access protected
	 */
	protected $tauxCache;
	
	
	/** Résistance de l'item
	 * @var int
	 * @access protected
	 */
	protected $resistance;
	
	
	/** Résistance Maximale de l'item.
	 * <br> Capacitée de l'item à subir des dommage avant de cesser d'être utilisable.
	 * @var int
	 * @access protected
	 */
	protected $resistanceMax;
	
	
	function __construct (&$arr)
	{
		$this->regrouper		= ($arr['db_regrouper']=='1') ? true : false;
		$this->nom 				= $arr['db_nom'];
		$this->description		= $arr['db_desc'] . ' ' . $arr['inv_extradesc'];
		$this->img				= $arr['db_img'];
		$this->pr				= $arr['db_pr'];
		$this->noteMjDb			= $arr['db_notemj'];
		$this->noteMjItem		= $arr['inv_notemj'];
		$this->qte				= $arr['inv_qte'];
		$this->equip			= $arr['inv_equip'];
		$this->invId 			= $arr['inv_id'];
		$this->dbId 			= $arr['db_id'];
		$this->idproprio 		= $arr['inv_persoid'];	
		$this->itemLieu 		= $arr['inv_lieutech'];	
		$this->itemBoutique 	= $arr['inv_boutiquelieutech'];
		$this->boutiquePrixVente= $arr['inv_boutiquePrixVente'];
		$this->boutiquePrixAchat= $arr['inv_boutiquePrixAchat'];
		$this->dbPrix 			= $arr['db_valeur'];
		$this->cacheNo			= $arr['inv_cacheno'];
		$this->tauxCache		= $arr['inv_cachetaux'];
		$this->resistance		= $arr['inv_resistance'];
		$this->resistanceMax	= $arr['db_resistance'];
	}

	/** Retourne si l'objet est caché ou non
	 * @return bool
	 */
	public function iscache()			{ return ($this->cacheNo != NULL); }
	
	/** Retourne le numero de cachette de l'objet
	 * @return int
	 */
	public function getCacheNo()		{ return $this->cacheNo; }
	
	/** Retourne le degre de réussite de la cachette
	 * @return int
	 */
	public function getTauxCache()		{ return $this->tauxCache; }
	
	 
	/** Cache un objet en y associant le taux de cachette et le numéro de cachette donnés en parametre
	 * @param int $tauxCache Le taux de réussite de la cachette
	 * @param int $cacheNo Le numéro de la cachette
	 */
	public function cacherObjet($tauxCache, $cacheNo)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
				. ' SET inv_cacheno=:cacheNo,'
					. ' inv_cachetaux=:cacheTaux'
				. ' WHERE inv_id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':cacheNo',	$cacheNo,	PDO::PARAM_INT);
		$prep->bindValue(':cacheTaux',	$tauxCache,	PDO::PARAM_INT);
		$prep->bindValue(':id',			$this->getInvId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
	
	/** Rendre un objet caché visible
	 */
	public function decacherObjet()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
				. ' SET inv_cacheno = NULL,'
					. ' inv_cachetaux = NULL'
				. ' WHERE inv_id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',			$this->getInvId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
	
		
	/** Retourne l'id de l'item.
	 * @return string
	 */
	public function getInvId()			{ return $this->invId; }

	/** Retourne l'id de l'item. dans le catalogue d'items
	 * @return string
	 */
	public function getDbId()			{ return $this->dbId; }
	
	/** Retourne l'id du propriétaire de l'item
	 * @return string
	 */
	public function getIdProprio()		{ return $this->idproprio; }
	
	/** Retourne le lieu de l'item (au sol)
	 * @return string
	 */
	public function getLieuItem()		{ return $this->itemLieu ; }

	/** Retourne le lieu de l'item (boutique)
	 * @return string
	 */
	public function getBoutiqueItem()		{ return $this->itemBoutique ; }
	
	/** Retourne le nom affichable de l'item.
	 * @return string
	 */
	public function getNom()			{ return stripslashes($this->nom); }
	
	/** Retourne la description affichable de l'item.
	 * @return string
	 */
	public function getDescription()	{ return stripslashes($this->description); }
	
	/** Retourne le nom du fichier de l'image représentant l'item.
	 * @return string
	 */
	public function getImage()			{ return $this->img; }
	
	/** Retourne le nombre de Points de Rangement qu'occupe l'item dans un inventaire.
	 * @return int
	 */
	public function getPr()				{ return $this->pr; }
	
	/** Retourne le nombre de points de résistance maximale que peut avoir de l'item.
	 * @return int
	 */
	public function getResistanceMax()	{ return $this->resistanceMax; }
	
	/** Retourne le nombre de points de résistance de l'item.
	 * @return int
	 */
	public function getResistance()		{ return $this->resistance; }
	
	/** Retourne la note cachée aux joueurs qu'un MJ peut laisser à propo de ce type d'item en général.
	 * @return string
	 */
	public function getNoteMjDb()		{ return stripslashes($this->noteMjDb); }
	
	/** Retourne la note cachée aux joueurs qu'un MJ  peut laisser à propo de cet item précis.
	 * @return string
	 */
	public function getNoteMjItem()		{ return stripslashes($this->noteMjItem); }
	
	/** Retourne la quantité de l'item pour tout les items ne supportant pas la gestion des quantité, soit 1.
	 * <br> (Cette méthode est overridée dans le cas ou l'item supportera une quantité)
	 * @return int
	 */
	public function getQte()			{ return $this->qte; }

	/** Modifie la quantité de l'item en inventaire
	 * @param int $qte Quantité de l'item
	 */	
	public function setQte($qte)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$this->qte = $qte;
		if($this->qte <= 0)
		{
			$query = 'DELETE FROM ' . DB_PREFIX . 'item_inv'
					. ' WHERE inv_id=:id'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':id',			$this->getInvId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
		else
		{
			$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					. ' SET inv_qte=:qte'
					. ' WHERE inv_id=:id'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':id',			$this->getInvId(),	PDO::PARAM_INT);
			$prep->bindValue(':qte',		$qte,				PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
		
	}
	
	/**Retourne le prix de base de l'item
	 *return int
	 */
	
	public function getDbPrix()			{ return $this->dbPrix;}
	
	
	/** Retourne le prix de vente de l'item en inventaire dans la boutique
	 * <br> (retourne NULL si l'item n'est pas dans une boutique)
	 * @param bool $format Formater ou non le nombre (Défaut= non, true=avec devise, false=false devise)
	 * @return float
	 */
	public function getBoutiquePrixVente($format=false) {
		if($format===false)
			return $this->boutiquePrixVente;
		
		return fctCreditFormat($this->boutiquePrixVente, $format);
	}
	
	/** Retourne le prix d'achat de l'item en inventaire dans la boutique
	 * <br> (retourne NULL si l'item n'est pas dans une boutique)
	 * @param bool $format Formater ou non le nombre (Défaut= non, true=avec devise, false=false devise)
	 * @return float
	 */
	public function getBoutiquePrixAchat($format=false) {
		if($format===false)
			return $this->boutiquePrixAchat;
		
		return fctCreditFormat($this->boutiquePrixAchat, $format);
	}
	
	
	/** Retourne TRUE si l'item est équipable, FALSE s'il n'est pas équipable
	 * <br> (Cette méthode est overridée dans le cas ou l'item supportera d'être équipé)
	 * @return bool
	 */
	public function canEquip()			{ return false; }
	
	/** Retourne TRUE si l'item est regroupable, FALSE s'il ne l'est pas.
	 * @return bool
	 */
	public function canRegroupe()		{ return $this->regrouper; }
	
	
	/** Retourne si l'arme est équipée sur le personnage ou non.
	 * @return bool
	 */
	public function isEquip()			{ return ($this->equip==1) ? true : false; }
	
	
	
	/** Change la résistance de l'item
	 * <br> Cette méthode calcule juste le nombre de résistance restant et met a jour l'objet.
	 * <br> IMPORTANT: pour sauvegarder en DB les modifications, vous DEVEZ apeller {@link setResistance()}.
	 * @param string $plusMoins '-' pour soustraire, '+' pour additionner
	 * @param int $nbrResistance Unité de résistance à modifier
	 */
	public function changeResistance($plusMoins, $nbrResistance)
	{
		if($plusMoins=='+')
		{
			if($this->resistance < $this->resistanceMax)
				if( ($this->resistance + $nbrResistance) <= $this->resistanceMax)
					$this->resistance+=$nbrResistance;
				else
					$this->resistance = $this->resistanceMax;
		}
		elseif($plusMoins=='-')
			$this->resistance-=$nbrResistance;
		
	}

	/** Met à jour la résistance de l'item
	 * @param int $qte Quantité de résistance
	 */	
	public function setResistance($qte=false)
	{
		if($qte===false)
			$qte = $this->getResistance();

		
		if ($qte<=$this->getresistanceMax())
		{
			$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
			$db = $dbMgr->getConn('game'); //Demander la connexion existante
			
			$this->resistance = $qte;
			$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
						. ' SET inv_resistance=:res'
						. ' WHERE inv_id=:id'
						. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':res',	$qte,				PDO::PARAM_INT);
			$prep->bindValue(':id',		$this->getInvId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
		}
	}
	
	
	
	
	/** Transfère un item d'un inventaire vers une boutique.
	 *
	 * @param object &$boutiqueTo 	Instance de la boutique de destination.
	 * @param int $qte				Quantité d'item(s) à transférer.
	 */ 
	public function transfererVersBoutique(&$boutiqueTo, $qte)
	{
		
		if ($qte > $this->getQte())
			die (fctErrorMsg('Vous ne pouvez pas transférer plus que l\'ensemble de départ contient.'));
		
		
		
		//Rechercher si un item identique est déjà présent dans l'inventaire du lieu d'arrivé
		$itemFound = self::itemIsInBoutique($this, $boutiqueTo);
		if($itemFound!==false || $this->canRegroupe())
		{
			//AVEC gestion des quantités
			if($itemFound!==false)
			{
				//L'item est déjà présent dans l'inventaire de la boutique, augmenter sa quantité
				$itemFound->setQte($itemFound->getQte()+$qte);
				
			}
			else
			{
				//Il s'agit d'un nouvel item
				//Dupliquer l'item de l'inventaire de l'inventaire de la boutique
				self::duplicateItem($this->getInvId(), $qte, null, null, $boutiqueTo->getNomTech(), null, null);
				
			}
			
			//Retirer les items à transférer de l'inventaire de départ
			$this->setQte($this->getQte()-$qte);
		}
		else
		{
			//SANS gestion des quantités donc transfert de la totalité et non groupable : tranfert simple
			
			//Il s'agit d'un nouvel item
			//Dupliquer l'item de l'inventaire de l'inventaire du donneur
			//self::duplicateItem($this->getInvId(), $qte, null, null, $boutiqueTo->getNomTech(), null, null);

			$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
			$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
			$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					. ' SET `inv_persoid` 			= NULL,'
						. ' `inv_lieutech`			= NULL,'
						. ' `inv_boutiquelieutech`	= :lieuTech,'
						. ' `inv_idcasier`			= NULL,'
						. ' `inv_equip`				= NULL,'
						. ' `inv_itemid`			= NULL,'
						. ' `inv_boutiquePrixVente` = -1,'
						. ' `inv_boutiquePrixAchat` = -1'
					. ' WHERE `inv_id`=:id'
					. ' LIMIT 1;';	
			$prep = $db->prepare($query);
			$prep->bindValue(':lieuTech',	$boutiqueTo->getNomTech(),	PDO::PARAM_STR);
			$prep->bindValue(':id',			$this->getInvId(),			PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
		
		
	}
	
	
	
	
	
	
	
	
	/** Transfère un item d'un inventaire vers un personnage.
	 *
	 * @param object &$persoTo 	Instance du personnage de destination.
	 * @param int $qte			Quantité d'item(s) à transférer.
	 */ 
	public function transfererVersPerso(&$persoTo, $qte)
	{
		
		if (!$this instanceof Member_Item)
			die(fctErrorMSG(__FILE__ . __LINE__ . ' : Variable non-item détectée; Ceci n\'est pas un item.'));
		
		if ($qte > $this->getQte())
			die (fctErrorMsg('Vous ne pouvez pas transférer plus que l\'ensemble de départ contient.'));
		
		
		//SANS gestion des quantités donc transfert de la totalité et non groupable : tranfert simple
		if (!$this->canRegroupe() && $this->getQte()==1)
		{
			$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
			$db = $dbMgr->getConn('game'); //Demander la connexion existante
			
			$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					. ' SET `inv_persoid` 			= :persoId ,'
						. ' `inv_lieutech`			= NULL,'
						. ' `inv_boutiquelieutech`	= NULL,'
						. ' `inv_equip`				= NULL,'
						. ' `inv_idcasier`			= NULL,'
						. ' `inv_itemid`			= NULL,'
						. ' `inv_cacheno`			= NULL,'
						. ' `inv_cachetaux`			= NULL,'
						. ' `inv_boutiquePrixVente` = NULL,'
						. ' `inv_boutiquePrixAchat` = NULL'
					. ' WHERE `inv_id`=:id'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId',	$persoTo->getId(),	PDO::PARAM_INT);
			$prep->bindValue(':id',			$this->getInvId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			return;
		}
		
		
		//AVEC gestion des quantités
		
		
		//Rechercher si un item identique est déjà présent dans l'inventaire du lieu d'arrivé
		$itemFound = self::itemIsInPerso($this, $persoTo);
		if(!$this->canRegroupe())
		{
			//L'item pourrait être groupé, mais n'a pas le droit de l'être
			// dans un inventaire perso. Le dégrouper.
			for($i=1;$i<=$qte;$i++)
				self::duplicateItem($this->getInvId(), 1, $persoTo->getId(), null, null, null, null);
			
		}
		elseif($itemFound===false)
		{
			//Il s'agit d'un nouvel item
			//Dupliquer l'item de l'inventaire de l'inventaire du donneur
			self::duplicateItem($this->getInvId(), $qte, $persoTo->getId(), null, null, null, null);
		}
		else
		{
			//L'item est déjà présent dans l'inventaire du perso, augmenter sa quantité
			$itemFound->setQte($itemFound->getQte()+$qte);
		}
		
		//Retirer les items à transférer de l'inventaire de départ
		$this->setQte($this->getQte()-$qte);
		
		
	}
	
	

	
	
	
	/** Transfère un item d'un inventaire vers  un lieu en le cachant
	 *
	 * @param object &$lieuTo 	Instance du lieu de destination
	 * @param int $qte			Quantité d'item(s) à cacher
	 * @param int $degre		Qualité de la cachette (en fonction du taux de réussite du jet de dé)
	 * @param int $cache_no		Numéro de cachette (permet d'accéder directement à la cachette sans fouiller tout un lieu)
	 */
	 public function cacherVersLieu(&$lieuTo, $qte, $degre, $cache_no)
	 {
	 
		if ($qte > $this->getQte())
			die (fctErrorMsg('Vous ne pouvez pas transférer plus que l\'ensemble de départ contient. (' . $qte . '>' . $this->getQte() . ')'));
			
			
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
			
		//SANS gestion des quantités donc transfert de la totalité et non groupable : tranfert simple
		if (!$this->canRegroupe() && $this->getQte()==$qte)
		{
					
			$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					. ' SET `inv_persoid` 			= NULL,'
						. ' `inv_lieutech`			= :lieuTech,'
						. ' `inv_boutiquelieutech`	= NULL,'
						. ' `inv_idcasier`			= NULL,'
						. ' `inv_equip`				= NULL,'
						. ' `inv_itemid`			= NULL,'
						. ' `inv_cacheno`			= :cacheNo,'
						. ' `inv_cachetaux`			= :cacheTaux'
					. ' WHERE `inv_id`=:id'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':lieuTech',	$lieuTo->getNomTech(),	PDO::PARAM_STR);
			$prep->bindValue(':cacheNo',	$cache_no,				PDO::PARAM_INT);
			$prep->bindValue(':cacheTaux',	$degre,					PDO::PARAM_INT);
			$prep->bindValue(':id',			$this->getInvId(),		PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			return;
		}
		
		//Avec gestion des quantités : On duplique dans tous les cas l'item, même s'il est déjà présent dans le lieu car l'item est caché.
		
		//On duplique l'item dans l'inventaire du lieu
		$newItemId = self::duplicateItem($this->getInvId(), $qte, null, $lieuTo->getNomTech(), null, null, null);
		
		//On modifi les parametres pour cacher l'item
		$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
				. ' SET `inv_cacheno`	= :cacheNo,'
					. ' `inv_cachetaux`	= :cacheTaux'
				. ' WHERE `inv_id`=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':cacheNo',	$cache_no,	PDO::PARAM_INT);
		$prep->bindValue(':cacheTaux',	$degre,		PDO::PARAM_INT);
		$prep->bindValue(':id',			$newItemId,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;

		
		
		//retirer les qte dans l'inventaire de départ
		$this->setQte($this->getQte()-$qte);
	}
	
	
	/** Transfère un item d'un inventaire vers un lieu.
	 *
	 * @param object &$lieuTo 	Instance du lieu de destination.
	 * @param int $qte			Quantité d'item(s) à transférer.
	 */ 
	public function transfererVersLieu(&$lieuTo, $qte)
	{
		
		if ($qte > $this->getQte())
			die (fctErrorMsg('Vous ne pouvez pas transférer plus que l\'ensemble de départ contient. (' . $qte . '>' . $this->getQte() . ')'));
		
		
		//SANS gestion des quantités donc transfert de la totalité et non groupable : tranfert simple
		if (!$this->canRegroupe() && $this->getQte()==$qte)
		{
			$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
			$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
			$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					. ' SET `inv_persoid` 			= NULL,'
						. ' `inv_lieutech`			= :lieuTech,'
						. ' `inv_boutiquelieutech`	= NULL,'
						. ' `inv_idcasier`			= NULL,'
						. ' `inv_equip`				= NULL,'
						. ' `inv_itemid`			= NULL'
					. ' WHERE `inv_id`=:id'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':lieuTech',	$lieuTo->getNomTech(),	PDO::PARAM_STR);
			$prep->bindValue(':id',			$this->getInvId(),		PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			return;
		}
		
		
		//AVEC gestion des quantités
		
		
		
		//Rechercher si un item identique est déjà présent dans l'inventaire du lieu d'arrivé
		$itemFound = self::itemIsInLieu($this, $lieuTo);
		if($itemFound===false || $itemFound->iscache()) //L'item est déjà présent ou alors l'item présent est caché (donc ne pas le regrouper avec un item non caché)
		{
			//Il s'agit d'un nouvel item
			//Dupliquer l'item de l'inventaire de l'inventaire du donneur
			self::duplicateItem($this->getInvId(), $qte, null, $lieuTo->getNomTech(), null, null, null);
		}
		else
		{
			//L'item est déjà présent dans l'inventaire du lieu, augmenter sa quantité
			$itemFound->setQte($itemFound->getQte()+$qte);
		}
		
		//Retirer les items à transférer de l'inventaire de départ
		$this->setQte($this->getQte()-$qte);
	}
	
	
	
	/** Transfère un item d'un inventaire vers un casier.
	 *
	 * @param object &$casierTo 	Instance du casier de destination.
	 * @param int $qte			Quantité d'item(s) à transférer.
	 */ 
	public function transfererVersCasier(&$casierTo, $qte)
	{
		
		if ($qte > $this->getQte())
			die (fctErrorMsg('Vous ne pouvez pas transférer plus que l\'ensemble de départ contient.'));
		
		
		//SANS gestion des quantités donc transfert de la totalité et non groupable : tranfert simple
		if (!$this->canRegroupe() && $this->getQte()==$qte)
		{
			$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
			$db = $dbMgr->getConn('game'); //Demander la connexion existante
			
			$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					. ' SET `inv_persoid` 			= NULL,'
						. ' `inv_lieutech`			= NULL,'
						. ' `inv_boutiquelieutech`	= NULL,'
						. ' `inv_idcasier`			= :casierId,'
						. ' `inv_equip`				= NULL,'
						. ' `inv_itemid`			= NULL'
					. ' WHERE `inv_id`=:id'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':casierId',	$casierTo->getId(),	PDO::PARAM_INT);
			$prep->bindValue(':id',			$this->getInvId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			return;
		}
		
		
		//AVEC gestion des quantités
		
		
		//Rechercher si un item identique est déjà présent dans l'inventaire du lieu d'arrivé
		$itemFound = self::itemIsInCasier($this, $casierTo);
		if($itemFound===false)
		{
			//Il s'agit d'un nouvel item
			//Dupliquer l'item de l'inventaire de l'inventaire du donneur
			self::duplicateItem($this->getInvId(), $qte, null, null, null, $casierTo->getId(), null);
		}
		else
		{
			//L'item est déjà présent dans l'inventaire du perso, augmenter sa quantité
			$itemFound->setQte($itemFound->getQte()+$qte);
		}
		
		
		//Retirer les items à transférer de l'inventaire de départ
		$this->setQte($this->getQte()-$qte);
		
	}
	
	
	/** Transfère un item d'un inventaire vers un autre item (ex.:sac).
	 *
	 * @param object &$persoTo 	Instance de l'item de destination.
	 * @param int $qte			Quantité d'item(s) à transférer.
	 */ 
	public function transfererVersItem(&$itemTo, $qte)
	{
		
		if ($qte > $this->getQte())
			die (fctErrorMsg('Vous ne pouvez pas transférer plus que l\'ensemble de départ contient.'));
		
		if($this->getInvId() == $itemTo->getInvId())
			die (fctErrorMsg('Vous ne pouvez pas transférer un item dans lui-même.'));
		
		//SANS gestion des quantités donc transfert de la totalité et non groupable : tranfert simple
		if (!$this->canRegroupe() && $this->getQte()==$qte)
		{
			$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
			$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
			$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					. ' SET `inv_persoid` 			= NULL,'
						. ' `inv_lieutech`			= NULL,'
						. ' `inv_boutiquelieutech`	= NULL,'
						. ' `inv_idcasier`			= NULL,'
						. ' `inv_equip`				= NULL,'
						. ' `inv_itemid`			= :itemId'
					. ' WHERE `inv_id`=:id'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':itemId',		$itemTo->getInvId(),PDO::PARAM_INT);
			$prep->bindValue(':id',			$this->getInvId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			return;
		}
		
		
		//AVEC gestion des quantités

		
		
		//Rechercher si un item identique est déjà présent dans l'inventaire du lieu d'arrivé
		$itemFound = self::itemIsInItem($this, $itemTo);
		if($itemFound===false)
		{
			//Il s'agit d'un nouvel item
			//Dupliquer l'item de l'inventaire de l'inventaire du donneur
			self::duplicateItem($this->getInvId(), $qte, null, null, null, null,$itemTo->getInvId());
		}
		else
		{
			//L'item est déjà présent dans l'inventaire du perso, augmenter sa quantité
			$itemFound->setQte($itemFound->getQte()+$qte);	
		}
		
		
		//Retirer les items à transférer de l'inventaire de départ
		$this->setQte($this->getQte()-$qte);
		
	}
	
	
	
	/** Duplique un item
	 * Cette méthode devrait être utilisée qu'en cas d'exception. Pour l'instant,
	 * seul la consommation de drogue l'utilise afin d'isoler un item en ignorant
	 * le support du groupement.
	 * 
	 * @param int $itemInvId				inv_id de l'item à dupliquer
	 * @param int $qte						Quantité à créer (défaut: 1)
	 * @param int $persoId					Affecter l'item à un personnage (défaut: null)
	 * @param string $lieuTech				Affecter l'item à un lieu (défaut: null)
	 * @param string $boutiqueLieuTech		Affecter l'item à une boutique (défaut: null)
	 * @param int $casierId					Affecter l'item à un casier (défaut: null)
	 * @param int $itemId					Affecter l'item à un item (défaut: null)
	 * @return int							Retourne l'id du duplicata (du nouvel item)
	 */
	 
	 //self::duplicateItem($this->getInvId(), $qte, $persoTo->getId(), null, null, null, null);
	 
	public static function duplicateItem($itemInvId, $qte=1, $persoId=null, $lieuTech=null, $boutiqueLieuTech=null, $casierId=null, $itemId=null)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Trouver les informations sur l'item à dupliquer
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'item_inv'
				. ' WHERE `inv_id`=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',	$itemInvId,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arr= $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr === false)
			die(fctErrorMSG('L\'item ID #' . (int)$itemInvId . ' n\'existe pas.'));
		
		
		//Configurer les valeurs en ce qui concerne les prix de vente en boutique (qui n'ont rien à voir avec la valeur de l'item)
		
		
		//Insérer le nouvel item 
		$query = 'INSERT INTO ' . DB_PREFIX . 'item_inv'
				. ' (	`inv_dbid`,				`inv_persoid`,			`inv_equip`,				`inv_lieutech`,			`inv_boutiquelieutech`,	`inv_idcasier`,	`inv_itemid`,'
					. ' `inv_desc`,			`inv_img`,				`inv_qte`,					`inv_munition`,			`inv_resistance`,			`inv_duree`,'
					. ' `inv_shock_pa`,		`inv_shock_pv`,		`inv_boost_pa`,			`inv_boost_pv`,			`inv_perc_stat_agi`,'
					. ' `inv_perc_stat_dex`,`inv_perc_stat_per`,	`inv_perc_stat_for`,		`inv_perc_stat_int`,		`inv_remiseleft`,'
					. ' `inv_pn`,				`inv_notel`,			`inv_memoiretext`,		`inv_nobanque`,			`inv_nocompte`,'
					. ' `inv_nocarte`,		`inv_nip`,				`inv_boutiquePrixVente`,`inv_boutiquePrixAchat`,`inv_param`,'
					. ' `inv_notemj`,			`inv_extradesc`,		`inv_cacheno`,				`inv_cachetaux`'
				. ' )'
				. ' VALUES'
				. ' ('
					. ' :inv_dbid,				:inv_persoid,			:inv_equip,					:inv_lieutech,				:inv_boutiquelieutech,	:inv_idcasier,	:inv_itemid,'
					. ' :inv_desc,				:inv_img,				:inv_qte,					:inv_munition,				:inv_resistance,			:inv_duree,'
					. ' :inv_shock_pa,		:inv_shock_pv,			:inv_boost_pa,				:inv_boost_pv,				:inv_perc_stat_agi,'
					. ' :inv_perc_stat_dex,	:inv_perc_stat_per,	:inv_perc_stat_for,		:inv_perc_stat_int,		:inv_remiseleft,'
					. ' :inv_pn,				:inv_notel,				:inv_memoiretext,			:inv_nobanque,				:inv_nocompte,'
					. ' :inv_nocarte,			:inv_nip,				:inv_boutiquePrixVente,	:inv_boutiquePrixAchat,	:inv_param,'
					. ' :inv_notemj,			:inv_extradesc,		:inv_cacheno,				:inv_cachetaux'
				. ' );';
		$prep = $db->prepare($query);
		$prep->bindValue(':inv_dbid', $arr['inv_dbid'], PDO::PARAM_INT);
		
		if(empty($persoId))
			$prep->bindValue(':inv_persoid',		NULL,					PDO::PARAM_NULL);
		else
			$prep->bindValue(':inv_persoid',		$persoId,				PDO::PARAM_INT);
		
		if(empty($lieuTech))
			$prep->bindValue(':inv_lieutech',		NULL,					PDO::PARAM_NULL);
		else
			$prep->bindValue(':inv_lieutech',		$lieuTech,				PDO::PARAM_STR);

		if(empty($boutiqueLieuTech))
			$prep->bindValue(':inv_boutiquelieutech',NULL,					PDO::PARAM_NULL);
		else
			$prep->bindValue(':inv_boutiquelieutech',$boutiqueLieuTech,		PDO::PARAM_STR);

		if(empty($casierId))
			$prep->bindValue(':inv_idcasier',		NULL,					PDO::PARAM_NULL);
		else
			$prep->bindValue(':inv_idcasier',		$casierId,				PDO::PARAM_INT);

		if(empty($itemId))
			$prep->bindValue(':inv_itemid',			NULL,					PDO::PARAM_NULL);
		else
			$prep->bindValue(':inv_itemid',			$itemId,				PDO::PARAM_INT);

		
		$prep->bindValue(':inv_qte',				$qte,					PDO::PARAM_INT);

		
		
		$tmp = array(
					'equip', 'munition', 'resistance', 'duree',
					'shock_pa','shock_pv','boost_pa','boost_pv',
					'perc_stat_agi', 'perc_stat_dex','perc_stat_per',
					'perc_stat_for', 'perc_stat_int',
					'remiseleft', 'desc', 'img', 'pn',
					'notel', 'memoiretext', 'nobanque',
					'nocompte','nocarte', 'nip', 'cacheno', 'cachetaux'
				);
		foreach($tmp as $val)
		{
			if($arr['inv_' . $val] === NULL)
				$prep->bindValue(':inv_' . $val,		NULL,					PDO::PARAM_NULL);
			else
				$prep->bindValue(':inv_' . $val,		$arr['inv_' . $val],	PDO::PARAM_INT);
		
		}


		
		if(empty($boutiqueLieuTech))
		{
			$prep->bindValue(':inv_boutiquePrixVente',	NULL,					PDO::PARAM_NULL);
			$prep->bindValue(':inv_boutiquePrixAchat',	NULL,					PDO::PARAM_NULL);
		}
		else
		{
			$prep->bindValue(':inv_boutiquePrixVente',	-1,						PDO::PARAM_INT);
			$prep->bindValue(':inv_boutiquePrixAchat',	-1,						PDO::PARAM_INT);
		}
		
		
		$prep->bindValue(':inv_param',				$arr['inv_param'],			PDO::PARAM_STR);
		$prep->bindValue(':inv_extradesc',			$arr['inv_extradesc'],		PDO::PARAM_STR);
		$prep->bindValue(':inv_notemj',				$arr['inv_notemj'],			PDO::PARAM_STR);
		
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		return $db->lastInsertId();
	}
	
	
	
	/** Détermine si un item est déjà existant dans l'inventaire d'un perso
	 *
	 * @param Member_Item &$ITEM Instance de l'item pour lequel on veux vérifier s'il est déjà présent dans un inventaire.
	 * @param Member_Perso &$INTO Instance du personnage dont l'inventaire est à vérifier.
	 * @return bool	Retourne TRUE si l'item est déjà présent dans l'inventaire.
	 */
	private static function itemIsInPerso(&$ITEM, &$INTO)
	{
		if (!$INTO instanceof Member_Perso)
			die(fctErrorMSG('Paramêtre $INTO n\'est pas une instance de la classe prévue. Utilisez la fonction adéquate.'));
		
		$i=0;
		$found=false;
		while( $item = $INTO->getInventaire($i++))
		{
			if($item->getDbId() == $ITEM->getDbId())
			{
				if($item instanceof Member_ItemDrogueDrogue)
					return self::itemsAreSameDrogue($item, $ITEM);
					
				return $item;
			}
		}
		
		return false;
	}
	
	/** Détermine si un item est déjà existant dans l'inventaire d'un lieu
	 *
	 * @param Member_Item &$ITEM Instance de l'item pour lequel on veux vérifier s'il est déjà présent dans un inventaire.
	 * @param Member_Lieu &$INTO Instance du lieu dont l'inventaire est à vérifier.
	 * @return bool	Retourne TRUE si l'item est déjà présent dans l'inventaire.
	 */
	private static function itemIsInLieu(&$ITEM, &$INTO)
	{
	
		if (!$INTO instanceof Member_Lieu)
			die(fctErrorMSG('Paramêtre $INTO n\'est pas une instance de la classe prévue. Utilisez la fonction adéquate.'));
		
		$i=0;
		$found=false;
		while( $item = $INTO->getItems($i++))
		{
			if($item->getDbId() == $ITEM->getDbId())
			{
				if($item instanceof Member_ItemDrogueDrogue)
					return self::itemsAreSameDrogue($item, $ITEM);
					
				return $item;
			}
		}
		
		return false;
	}
	
	/** Détermine si un item est déjà existant dans l'inventaire d'une boutique
	 *
	 * @param Member_Item &$ITEM Instance de l'item pour lequel on veux vérifier s'il est déjà présent dans un inventaire.
	 * @param Member_Lieu &$INTO Instance du lieu (avec une boutique) dont l'inventaire est à vérifier.
	 * @return mixed Retourne l'item de la boutique si correspondance, false sinon
	 */
	private static function itemIsInBoutique(&$ITEM, &$INTO)
	{
	
		if (!$INTO instanceof Member_Lieu)
			die(fctErrorMSG('Paramêtre $INTO n\'est pas une instance de la classe prévue. Utilisez la fonction adéquate.'));
		
		$i=0;
		$found=false;
		while( $item = $INTO->getBoutiqueInventaire($i++))
		{
			if($item->getDbId() == $ITEM->getDbId())
			{
				if($item instanceof Member_ItemMunition)
					return $item;
				
				if($item instanceof Member_ItemDrogueDrogue)
					if(self::itemsAreSameDrogue($item, $ITEM))
						return $item;

				if($item instanceof Member_ItemAutre)
						return $item;
				
				if($item instanceof Member_ItemNourriture)
					if($item->getPn()==$ITEM->getPn())
						return $item;

				if($item instanceof Member_ItemDefense)
					if($item->getResistance()==$ITEM->getResistance())
						return $item;

				if($item instanceof Member_ItemArme)
					if($item->getResistance()==$ITEM->getResistance())
						return $item;
						
				if($item instanceof Member_ItemClef)
					if($item->getCode()==$ITEM->getCode())
						return $item;

				if($item instanceof Member_Badge)
					if($item->getNom()==$ITEM->getNom())
						if($item->getTitre()==$ITEM->getTitre())
							return $item;
							
				if($item instanceof Member_ItemTrousse)
					if($item->getResistance()==$ITEM->getResistance())
						return $item;
				
				if($item instanceof Member_ItemNourriture)
					if($item->getPn()==$ITEM->getPn())
						return $item;
				
			}
		}
		
		return false;
	}
	
	
	/** Détermine si un item est déjà existant dans l'inventaire d'un casier
	 *
	 * @param Member_Item &$ITEM Instance de l'item pour lequel on veux vérifier s'il est déjà présent dans un inventaire.
	 * @param Member_Casier &$INTO Instance du casier dont l'inventaire est à vérifier.
	 * @return bool	Retourne TRUE si l'item est déjà présent dans l'inventaire.
	 */
	private static function itemIsInCasier(&$ITEM, &$INTO)
	{
		
		if (!$INTO instanceof Member_Casier)
			die(fctErrorMSG('Paramêtre $INTO n\'est pas une instance de la classe prévue. Utilisez la fonction adéquate.'));
		
		$i=0;
		$found=false;
		while( $item = $INTO->getInventaire($i++))
		{
			if($item->getDbId() == $ITEM->getDbId())
			{
				if($item instanceof Member_ItemDrogueDrogue)
					return self::itemsAreSameDrogue($item, $ITEM);
					
				return $item;
			}
		}
		
		return false;
	}
	
	/** Détermine si un item est déjà existant dans l'inventaire d'un autre item (ex.: sac)
	 *
	 * @param Member_Item &$ITEM Instance de l'item pour lequel on veux vérifier s'il est déjà présent dans un inventaire.
	 * @param Member_Item &$INTO Instance de l'item dont l'inventaire est à vérifier.
	 * @return bool	Retourne TRUE si l'item est déjà présent dans l'inventaire.
	 */
	private static function itemIsInItem(&$ITEM, &$INTO)
	{
		if (!$INTO instanceof Member_ItemSac) //Seuls les items de la classe sac possèdent la méthode getInventaire pour le moment.
			die(fctErrorMSG('Paramêtre $INTO n\'est pas une instance de la classe prévue. Utilisez la fonction adéquate.'));
		
		$i=0;
		$found=false;
		while( $item = $INTO->getInventaire($i++))
		{
			if($item->getDbId() == $ITEM->getDbId())
			{
				if($item instanceof Member_ItemDrogueDrogue)
					return self::itemsAreSameDrogue($item, $ITEM);
					
				return $item;
			}
		}
		
		return false;
	}
	
	/** Détermine si 2 drogues sont identiques
	 *
	 * @param Member_ItemDrogueDrogue &$a 	Item #1 à comparer
	 * @param Member_ItemDrogueDrogue &$b	Item #2 à comparer
	 * @return bool			Retourne $a si les 2 items sont égaux, FALSE dans le cas contraire.
	 */
	private static function itemsAreSameDrogue(&$a, &$b)
	{
		return (
					$a->isEquip() === false
					&& $b->isEquip() === false
					&& $a->getDuree() == $b->getDuree()
					&& $a->getBoostPa() == $b->getBoostPa()
					&& $a->getBoostPv() == $b->getBoostPv()
					&& $a->getShockPa() == $b->getShockPa()
					&& $a->getShockPv() == $b->getShockPv()
					&& $a->getPercStatAgi() == $b->getPercStatAgi()
					&& $a->getPercStatDex() == $b->getPercStatDex()
					&& $a->getPercStatPer() == $b->getPercStatPer()
					&& $a->getPercStatFor() == $b->getPercStatFor()
					&& $a->getPercStatInt() == $b->getPercStatInt()
				) ? $a : false;
	}
	
	public function equiper()
	{
		$this->setEquip(1);
	}
	
	public function desequiper()
	{
		$this->setEquip(0);
	}
	
	private function setEquip($equip)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
	
		$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
				. ' SET inv_equip=:equip'
				. ' WHERE inv_persoid=:persoId'
					. ' AND inv_id=:itemId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',		$this->getIdProprio(),	PDO::PARAM_INT);
		$prep->bindValue(':itemId',			$this->getInvId(),		PDO::PARAM_INT);
		$prep->bindValue(':equip',			$equip,					PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
}


