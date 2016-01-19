<?php
/** Gestion des comptes bancaire. 
 * 
 * Exemple d'utilisation - Afficher le solde d'un compte à partir d'une carte de guichet:
 * <code>
 * $query = 'SELECT *'
 * 		. ' FROM ' . DB_PREFIX . 'banque_cartes'
 * 		. ' LEFT JOIN ' . DB_PREFIX . 'banque_comptes'
 *			. ' ON (compte_banque = carte_banque AND compte_compte = carte_compte)'
 * 		. ' WHERE carte_id = :carteId'
 * 			LIMIT 1;';
 *
 * 	$prep = $db->prepare($query);
 *	$prep->bindValue(':carteId',	$_POST['carteid'],	PDO::PARAM_INT);
 *	$prep->execute($db, __FILE__, __LINE__);
 *	$arr = $prep->fetch();
 *	$prep->closeCursor();
 *	$prep = NULL;
 *
 *	if ($arr === false)
 * 		ErrorMSG('Cette carte n\'existe pas.');
 * 
 * $compte = new Member_BanqueCompte($arr);
 * echo $compte->getCash();
 * </code>
 *
 * @package Member
 * @subpackage Banque
 */
class Member_BanqueCompte
{

	/** Id dans la table compte.
	 * @var int
	 * @access private
	 */
	private $id;
	
	/** Id du perso à qui le compte est attribué.
	 * @var int
	 * @access private
	 */
	private $idPerso;
	
	/** Nom du compte (pas nécéssairement le nom du perso)
	 * @var string
	 * @access private
	 */
	private $nom;
	
	/** Numéro de la banque (pas son ID)
	 * @var int
	 * @access private
	 */
	private $noBanque;
	
	/** Numéro du compte au format XXXX-XXXX-XXXX (pas son ID)
	 * @var string
	 * @access private
	 */
	private $noCompte;
	
	/** Solde du compte
	 * @var float
	 * @access private
	 */
	private $cash;
	
	/** Autorisation du compte à effectuer des transactions automatiques
	 * @var bool
	 * @access private
	 */
	private $authTransAuto;
	
	/** Code d'accès physique au compte (NIP principal  du compte)
	 * @var int
	 * @access private
	 */
	private $nip;
	
	
	/** Tableau des cartes appartenant associées au compte
	 * @var array
	 * @access private
	 */
	private $arrCarte;
	
	/** Tableau des transactions automatiques dont le compte est emetteur
	 * @var array
	 * @access private
	 */
	private $arrTransactions;
	
	function __construct (&$arr)
	{
		$this->id 				= $arr['compte_id'];
		$this->idPerso			= $arr['compte_idperso'];
		$this->nom				= $arr['compte_nom'];
		$this->noBanque			= $arr['compte_banque'];
		$this->noCompte			= $arr['compte_compte'];
		$this->cash				= $arr['compte_cash'];
		$this->authTransAuto	= ($arr['compte_auth_auto_transaction'] == 1)? true : false;
		$this->nip				= $arr['compte_nip'];
		$this->arrTransactions	= array();
		$this->arrCarte			= array();
	}
	
	/** Constructeur à partir du numéro de compte complet (#banque-#compte)
	 * <br> Si le # est invalide, la fonction lève une exception GameException 
	 * <br> avec comme message : "Numéro de compte (<#compte>) invalide".
	 * <br> Si aucun compte n'existe, la fonction lève une exception GameException
	 * <br> avec comme message : "Le compte <#compte> n'existe pas".
	 * @var string numéro complet du compte (#banque-#compte)
	 * @return object retourne le compte correspondant
	 */
	public static function getCompteFromNoCompte($noCompte)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		//Valider le # du compte (TODO: REGEX !!!!)
		if(strlen($noCompte)!=19)
			throw new GameException('Numéro de compte(' . $noCompte . ') invalide.');
		
		//Séparrer le # de compte en segments
		$banque_no = substr($noCompte,0,4);
		$compte_no = substr($noCompte,5,14);
		
		//Rechercher le compte afin d'y faire des opérations.
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE	compte_banque=:banque'
					. ' AND compte_compte=:compte'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banque',	$banque_no,	PDO::PARAM_STR);
		$prep->bindValue(':compte',	$compte_no,	PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		//Valider si le compte existe
		if($arr === false)
			throw new GameException('Le compte '. $noCompte . ' n\'existe pas.');
		
		//Retourner l'instance du compte
		return new Member_BanqueCompte($arr);
	}
	
	/** Constructeur à partir de l'id d'un compte
	 * <br> Si aucun compte ne correspond à l'id, la fonction lève une exception de type GameException
	 * <br> avec comme message : "Le compte <id du compte> n'existe pas".
	 * @var int id du compte
	 * @return object retourne le compte correspondant
	 */
	 public static function getCompteFromId($id)
	 {
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE compte_id=:compteId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compteId', $id, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		
		if ($arr === false)
			throw new GameException('Le compte ' . $id . ' n\'existe pas.');
		else
			return new Member_BanqueCompte($arr);
	 }
	
	/** Retourne le tableau des cartes associées au compte
	 * 
	 * @return array retourne le tableau des cartes instanciées en objets de type Member_BanqueCarte
	 */
	public function getCartes()
	{
		if(empty($this->arrCarte))
		{
			$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
			$db = $dbMgr->getConn('game'); //Demander la connexion existante
			
			$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'banque_cartes'
					. ' WHERE	carte_banque=:banque'
					. ' AND carte_compte=:compte;';
			$prep = $db->prepare($query);
			$prep->bindValue(':banque',		$this->noBanque,		PDO::PARAM_STR);
			$prep->bindValue(':compte',		$this->noCompte,		PDO::PARAM_STR);
			$prep->execute($db, __FILE__, __LINE__);
			$arrAll = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;

			if(count($arrAll) != 0)
			{
				foreach($arrAll as $arr)
					$this->arrCarte[] = new Member_BanqueCarte($arr);
			}
		}
		return $this->arrCarte;
	}
	
	/** Retourne une carte appartenant au compte
	 * <br> Si la carte n'existe pas, la méthode va essayer de l'instancier
	 * <br> et de l'associer au compte de l'objet courrant.
	 * <br> NOTE: La fonction NE VALIDE PAS si la carte appartiend bel et bien à ce compte.
	 * @return object retourne un objet de type Member_BanqueCarte
	 */
	public function getCarte($carteNo, $arr=null)
	{
		if(!empty($this->arrCarte))
			foreach($this->arrCarte as $carte)
				if($carte->getNo() == $carteNo)
					return $carte;
				
		//Non trouvé, charger la carte
		if (empty($arr))
		{
			$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
			$db = $dbMgr->getConn('game'); //Demander la connexion existante
			
			
			$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'banque_cartes'
					. ' WHERE carte_id=:carteId'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':carteId',	$carteNo,	PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$arr = $prep->fetch();
			
			if ($arr === false)
				die('La carte demandé n\'existe pas dans la base de donnée');
			
		}
		
		$carte = new Member_BanqueCarte($arr);
		$this->arrCarte[] = $carte;
		return $carte;
	}
	
	
	/** Retourne l'id du compte
	 * @return int
	 */
	public function getId()					{ return $this->id; }
	
	/** Retourne l'id du perso associé au compte
	 * @return int
	 */
	public function getIdPerso()			{ return $this->idPerso; }
	
	/** Retourne le nom personnalisable du compte (pas nécéssairement le nom du perso)
	 * @return string
	 */
	public function getNom()				{ return $this->nom; }
	
	/** Retourne le numéro de la banque auquel le compte appartiend (pas son ID).
	 * @return int
	 */
	public function getNoBanque()			{ return $this->noBanque; }
	
	/** Retourne le numéro (pas son ID) du compte au format XXXX-XXXX-XXXX.
	 * @return string
	 */
	public function getNoCompte() 			{ return $this->noCompte; }
	
	/** Retourne le solde du compte.
	 * @return float
	 */
	public function getCash()				{ return $this->cash; }
	
	/** Retourne si le compte peut faire des transactions automatiques
	 * @return bool
	 */
	public function authAutoTransactions()	{ return $this->authTransAuto; }
	
	/** Retourne le Numéro d'Identification Personnel (NIP) du compte.
	 * <br> utile pour les accès physique (sur place). Les cartes possèdent leurs propres NIP individuel.
	 * @return int
	 */
	public function getNip()				{ return $this->nip; }
	
	
	/** Modifier le solde du compte.
	 * <br> Cette méthode s'occupe de calculer elle même le solde.
	 * <br> IMPORTANT: pour sauvegarder en DB les modifications, vous DEVEZ apeller {@link setCash()}.
	 * @param char $plusMoins Prend la valeur '+' ou '-' selon si on désire ajouter ou retirer de l'argent
	 * @param float $montant Montant d'argent  à ajouter ou retirer
	 */
	public function changeCash($plusMoins, $montant)
	{
		if($this->cash != -1)
		{
			if($plusMoins=='+')
				$this->cash+=$montant;
			elseif($plusMoins=='-')
				$this->cash-=$montant;
		}
	}
	
	/** Modifier directement le solde du compte.
	 * <br> Cette méthode modifie directement le solde du compte
	 * <br> NOTE: La méthode {@link changeCash()} permet d'effectuer automatiquement les calculs de solde
	 * @param float $montant Montant auquel placer le solde du compte
	 */
	public function setCash($montant = false)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
			
		
		if($montant === false)
			$montant = $this->cash;
		
		$query = 'UPDATE ' . DB_PREFIX . 'banque_comptes '
				. ' SET compte_cash=:cash'
				. ' WHERE compte_banque=:noBanque'
					. ' AND compte_compte=:noCompte;';
		$prep = $db->prepare($query);
		$prep->bindValue(':cash',		$montant,			PDO::PARAM_INT);
		$prep->bindValue(':noBanque',	$this->noBanque,	PDO::PARAM_STR);
		$prep->bindValue(':noCompte',	$this->noCompte,	PDO::PARAM_STR);
		$prep->execute($db, __FILE__,__LINE__);
		
		$this->cash = $montant;
	}
	
	
	/** Ajouter une transaction à l'historique des transactions du compte.
	 * @param string $from # du compte qui envoi l'argent (optionel)
	 * @param string $code Code à 4 caractères représentant le type de transaction
	 * @param string $retrait montant retiré du compte (0 par défaut)
	 * @param string $depot montant déposé dans le compte (0 par défaut)
	 */
	public function add_bq_hist($from='', $code, $retrait=0, $depot=0)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante

		$compte = $this->getNoBanque() . '-' . $this->getNoCompte();
		
		$query = 'INSERT INTO ' . DB_PREFIX . 'banque_historique'
				. ' (`compte`,`date`,`compte2`,`code`,`retrait`,`depot`,`solde`)'
				. ' VALUES'
				. ' (:compte, :date, :compte, :code, :retrait, :depot, :solde);';
		$prep = $db->prepare($query);
		$prep->bindValue(':compte',		$compte,			PDO::PARAM_STR);
		$prep->bindValue(':date',		time(),				PDO::PARAM_INT);
		$prep->bindValue(':compte',		$from,				PDO::PARAM_STR);
		$prep->bindValue(':code',		$code,				PDO::PARAM_STR);
		$prep->bindValue(':retrait',	$retrait,			PDO::PARAM_INT);
		$prep->bindValue(':depot',		$depot,				PDO::PARAM_INT);
		$prep->bindValue(':solde',		$this->getCash(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		
		
	}
	
	
	public static function generateAccountNo()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$accountno =	   rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9);
		$accountno.= "-" . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9);
		$accountno.= "-" . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9);
		
		$query = 'SELECT compte_id'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE compte_compte LIKE :compteNo'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compteNo',		"%{$accountno}%",	PDO::PARAM_STR);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		
		
		if ($arr !== false)
			return GenerateAccountNo();
		
		return $accountno;
	}
	
	/** Retourne la liste des transactions automatiques dont le compte est l'emetteur sous forme de tableau
	 * @return array tableau d'objets de type Member_BanqueTransactionAuto
	 */
	public function getTransactionsAuto()
	{
		if(empty($this->arrTransactions))
		{
			$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
			$db = $dbMgr->getConn('game'); //Demander la connexion existante
			
			$query = 'SELECT * FROM `' . DB_PREFIX . 'banque_transactions`'
					. ' WHERE `transaction_compte_from` = :transaction_compte_from;';
			$prep = $db->prepare($query);
			$prep->bindValue(':transaction_compte_from', $this->id, PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			
			$arrAll = $prep->fetchAll();
			
			if(count($arrAll) > 0)
			{
				foreach($arrAll as $arr)
				{
					$this->arrTransactions[] = new Member_BanqueTransactionAuto($arr);
				}
			}
		}
		
		return $this->arrTransactions;
	}
	
	/** Crée un nouvel accès de carte bancaire pour le compte
	 * @param string $name nom de la carte
	 * @param int $nip NIP de la carte
	 * @param bool $valid si l'accès est valide ou pas
	 * @return int retourne l'id de l'accès créé
	 */
	public function createAccesCard($name, $nip, $valid)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
	
		$query = 'INSERT INTO ' . DB_PREFIX . 'banque_cartes'
				. ' (`carte_banque`,`carte_compte`,`carte_nom`,`carte_nip`,`carte_valid`)'
				. ' VALUES'
				. ' (:banque, :compte, :nom, :nip, :valid);';
		$prep = $db->prepare($query);
		$prep->bindValue(':banque',	$this->noBanque,	PDO::PARAM_STR);
		$prep->bindValue(':compte',	$this->noCompte,	PDO::PARAM_STR);
		$prep->bindValue(':nom',	$name,					PDO::PARAM_STR);
		$prep->bindValue(':nip',	$nip,					PDO::PARAM_INT);
		$prep->bindValue(':valid',	$valid,					PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		return $db->lastInsertId();
	}
	
	/** Supprime un accès de carte bancaire pour le compte
	 * @param int $carteId id de la carte à supprimer
	 */
	public function delAccesCard($carteId)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
	
		$query = 'DELETE FROM ' . DB_PREFIX . 'banque_cartes'
				. ' WHERE	carte_banque=:banque'
					. ' AND carte_compte=:compte'
					. ' AND carte_id=:carte'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banque',		$this->noBanque,	PDO::PARAM_STR);
		$prep->bindValue(':compte',		$this->noCompte,	PDO::PARAM_STR);
		$prep->bindValue(':carte',		$carteId,			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
	
	/** Crée un item carte bancaire pour le compte
	 * @param string $name nom de la carte
	 * @param int $carteId Id de l'accès carte correspondant
	 * @param int $persoId Id du personnage qui va recevoir la carte
	 */
	public function createCarteGuichet($name, $carteId, $persoId)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		//Créer l'item carte de guichet
		$query = 'INSERT INTO ' . DB_PREFIX . 'item_inv'
				. ' (`inv_dbid`,`inv_persoid`,`inv_nobanque`,`inv_nocompte`,`inv_nocarte`,`inv_qte`,`inv_param`)'
				. ' VALUES '
				. ' (3, :persoId, :banque, :compte, :carte, 1, :nom);';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$persoId,				PDO::PARAM_INT);
		$prep->bindValue(':banque',		$this->noBanque,	PDO::PARAM_STR);
		$prep->bindValue(':compte',		$this->noCompte,	PDO::PARAM_STR);
		$prep->bindValue(':carte',		$carteId,				PDO::PARAM_INT);
		$prep->bindValue(':nom',		$name,					PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
	
	/** Supprime une transaction automatique pour le compte
	 * @param int $transactionId Id de la transaction à supprimer
	 */
	public function delTransactionAuto($transactionId)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
	
		$query = 'DELETE FROM ' . DB_PREFIX . 'banque_transactions'
				. ' WHERE	transaction_compte_from=:banque'
					. ' AND transaction_id=:transaction'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banque',			$this->id,		PDO::PARAM_INT);
		$prep->bindValue(':transaction',	$transactionId,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}

	/** Change le NIP du compte.
	 * <br> La fonction change directement le compte dans la base de donnée.
	 * @param int $newNIP Nouveau NIP.
	 */
	public function changeNIP($newNIP)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
			
		$query = 'UPDATE ' . DB_PREFIX . 'banque_comptes '
				. ' SET compte_nip = :nip'
				. ' WHERE compte_id = :compteId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':nip',		$newNIP,		PDO::PARAM_INT);
		$prep->bindValue(':compteId',	$this->id,		PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);

		$this->nip = $newNIP;
	}
}


