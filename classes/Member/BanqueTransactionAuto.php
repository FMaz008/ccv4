<?php
/**
 *
 * Gestion des transactions automatiques compte à compte
 *
 * @package Member
 * @subpackage Banque
 */
 
 class Member_BanqueTransactionAuto
 {
	/** Id dans la table transaction.
	 * @var int
	 * @access private
	 */
	private $id;
	
	/** Id du compte emetteur.
	 * @var int
	 * @access private
	 */
	private $compteIdFrom;
	
	/** Id du compte recepteur.
	 * @var int
	 * @access private
	 */
	private $compteIdTo;
	
	/** Valeur de la transaction.
	 * @var float
	 * @access private
	 */
	private $value;
	
	/** Description complémentaire associée à la transaction.
	 * @var string
	 * @access private
	 */
	private $description;
	
	/** Date de la prochaine transaction à effectuer (tous les mois).
	 * @var int
	 * @access private
	 */
	private $date;
	
	function __construct($arr)
	{
		$this->id = 			$arr['transaction_id'];
		$this->compteIdFrom = 	$arr['transaction_compte_from'];
		$this->compteIdTo = 	$arr['transaction_compte_to'];
		$this->value = 			$arr['transaction_valeur'];
		$this->description = 	$arr['transaction_description'];
		$this->date = 			$arr['transaction_date'];
	}
	
	/** Constructeur à partir de l'id de la transaction automatique
	 * <br> Si aucune transaction ne correspond avec l'id, la fonction lève une exception de type GameException
	 * <br> avec comme message : "La transaction <id de la transaction> n'existe pas".
	 * @var int id de la transaction
	 * @return object retourne la transaction correspondante
	 */
	public static function getTransactionAutoFromId($id)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_transactions'
				. ' WHERE transaction_id=:transactionId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':transactionId', $id, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		
		if ($arr === false)
			throw new GameException('La transaction ' . $id . ' n\'existe pas.');
		else
			return new Member_BanqueTransactionAuto($arr);
	}
	
	/** Crée une transaction automatique dans la base de donnée
	 * <br> Si une des valeurs est incohérente (vide ou erronée), la fonction
	 * <br> lève une exception de type GameException avec un message expliquant
	 * <br> la cause de l'erreur.
	 * @var int id du compte emetteur (doit être un compte valide)
	 * @var int id du compte recepteur (doit être un compte valide)
	 * @var float valeur de la transaction(doit être supérieure à 0)
	 * @var string description de la transaction (ne doit pas être vide)
	 * @var int date de la prochaine transaction (doit être dans le futur)
	 * @return object  retourne la transaction créée, de type Member_BanqueTransactionAuto
	 */
	public static function createNewTransactionAuto($compteIdFrom, $compteIdTo, $value, $description, $date)
	{
		$compteFrom = Member_BanqueCompte::getCompteFromId($compteIdFrom);
		$compteTo = Member_BanqueCompte::getCompteFromId($compteIdTo);
		
		if($value < 0)
			throw new GameException('La valeur d\'une transaction ne peut être négative.');
			
		if(empty($description))
			throw new GameException('La description d\'une transaction ne peut être vide.');
			
		if($date < $timeNow = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")))
			throw new GameException('La date d\'une transaction doit être dans le futur.');
			
		/* Créer la transaction */
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'INSERT INTO `' . DB_PREFIX . 'banque_transactions`'
				. ' (`transaction_compte_from`, `transaction_compte_to`, `transaction_valeur`, `transaction_description`, `transaction_date`)'
				. ' VALUES'
				. ' (:transactionIdFrom, :transactionIdTo, :transactionValue, :transactionDescription, :transactionDate);';
		$prep = $db->prepare($query);
		$prep->bindValue(':transactionIdFrom', $compteIdFrom, PDO::PARAM_INT);
		$prep->bindValue(':transactionIdTo', $compteIdTo, PDO::PARAM_INT);
		$prep->bindValue(':transactionValue', $value, PDO::PARAM_INT);
		$prep->bindValue(':transactionDescription', $description, PDO::PARAM_STR);
		$prep->bindValue(':transactionDate', $date, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		
		$transactionId = $db->lastInsertId();
		$arr = array(	'transaction_id' 			=> $transactionId,
						'transaction_compte_from' 	=> $compteIdFrom,
						'transaction_compte_to' 	=> $compteIdTo,
						'transaction_valeur' 		=> $value,
						'transaction_description' 	=> $description,
						'transaction_date' 			=> $date
					);
		return new Member_BanqueTransactionAuto($arr);
	}
	
	/* Accesseurs */
	
	/** Retourne l'id de la transaction
	 * @return int l'id de la transaction
	 */
	public function getId() 			{ return $this->id; }
	
	/** Retourne l'id du compte emetteur
	 * @return int id du compte emetteur
	 */
	public function getCompteIdFrom() 	{ return $this->compteIdFrom; }
	
	/** Retourne l'id du compte recepteur
	 * @return int id du compte recepteur
	 */
	public function getCompteIdTo() 	{ return $this->compteIdTo; }
	
	/** Retourne le numéro de compte du compte recepteur
	 * @return string #compte du compte recepteur
	 */
	public function getCompteNoTo()
	{
		$compteTo = Member_BanqueCompte::getCompteFromId($this->compteIdTo);
		return $compteTo->getNoBanque() . '-' . $compteTo->getNoCompte();
	}
	
	/** Retourne la valeur de la transaction
	 * @return int valeur de la transaction
	 */
	public function getValue() 			{ return $this->value; }
	
	/** Retourne la description associée à la transaction
	 * @return string description associée
	 */
	public function getDescription() 	{ return $this->description; }
	
	/** Retourne la date de la prochaine transaction à effectuer
	 * @return int date de la prochaine transaction à effectuer
	 */
	public function getDate() 			{ return $this->date; }
	
	/** Retourne la date de la prochaine transaction à effectuer
	 * au format affichable y/m/d h:m
	 * @return string
	 */
	public function getDateTxt()
	{
		return fctToGameTime($this->date);
	}
	
	/* Accesseurs de modification */
	
	/** Change l'id du compte recepteur
	 * <br> Si l'id ne correspond à aucun compte, la fonction lève une exception de type GameException
	 * <br> avec comme message : "Le compte <id du compte> n'existe pas".
	 * @var int id du nouveau compte recepteur
	 */
	public function changeCompteIdTo($id)
	{ 
		$newCompteTo = Member_BanqueCompte::getCompteFromId($id);
		$this->compteIdTo = $newCompteTo->getId(); 
	}
	
	/** Change la valeur de la transaction
	 * <br> Si la valeur est négative, la fonction lève une exception de type GameException
	 * <br> avec comme message : "La valeur d'une transaction ne peut être négative".
	 * @var float nouvelle valeur
	 */
	public function changeValue($value)
	{
		if($value < 0)
			throw new GameException('La valeur d\'une transaction ne peut être négative.');
		else
			$this->value = $value;
	}
	
	/** Change la description associée à la transaction
	 * <br> Si la description est vide, la fonction lève une exception de type GameException
	 * <br> avec comme message : "La description de la transaction ne peut être vide".
	 * @var string nouvelle description
	 */
	public function changeDescription($description)
	{ 
		if(empty($description))
			throw new GameException('La description de la transaction ne peut être vide.');
		else
			$this->description = $description;
	}
	
	/** Change la date de la prochaine transaction à effectuer
	 * <br> Si la date est dans le passé, la fonction lève une exception de type GameException
	 * <br> avec comme message : "La date d'une transaction doit être dans le futur".
	 * @var int nouvelle date 
	 */
	public function changeDate($date)
	{ 
		if($date < $timeNow = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")))
			throw new GameException('La date d\'une transaction doit être dans le futur.');
			
		$this->date = $date;
	}
	
	/** Sauvegarder les valeurs de la transaction dans la base de donnée
	 * 
	 */
	public function setData()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante

		$query = 'UPDATE ' . DB_PREFIX . 'banque_transactions '
				. ' SET transaction_compte_to = :transaction_compte_to,'
				. ' transaction_valeur = :transaction_valeur,'
				. ' transaction_description = :transaction_description,'
				. ' transaction_date = :transaction_date'
				. ' WHERE transaction_id = :transaction_id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':transaction_compte_to', $this->compteIdTo, PDO::PARAM_INT);
		$prep->bindValue(':transaction_valeur', $this->value, PDO::PARAM_INT);
		$prep->bindValue(':transaction_description', $this->description, PDO::PARAM_STR);
		$prep->bindValue(':transaction_date', $this->date, PDO::PARAM_INT);
		$prep->bindValue(':transaction_id', $this->id, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
	}

	/** Récupérer les transactions qui doivent être faites
	 * <br> La fonction lève une exception de type Exception si la limite de transaction est inférieure
	 * <br> ou égal à 0.
	 * @var int nombre de transactions maximum à récupérer
	 * @return array tableau contenant les transactions à faire
	 */
	public static function getTransactionsReady($limitTransaction)
	{
		if($limitTransaction <= 0)
			throw new Exception('limitTransaction = ' . $limitTransaction . ' <= 0');
	
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'SELECT * FROM `' . DB_PREFIX . 'banque_transactions`'
				. ' WHERE `transaction_date` < UNIX_TIMESTAMP()'
				. ' LIMIT :limit;';
		$prep = $db->prepare($query);
		$prep->bindValue(':limit', $limitTransaction, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arrAll = $prep->fetchAll();
		
		$result = array();
		foreach($arrAll as $arr)
			$result[] = new Member_BanqueTransactionAuto($arr);
			
		return $result;
	}

	/** Effectuer la transaction
	* <br> La date pour la prochaine transaction est repoussée au moins suivant.
	* <br> La fonction peut lever une exception de type GameException si un des comptes
	* <br> n'est associé à rien.
	*/
	public function effectuerTransaction()
	{
		/* Instances des comptes concernés */
		$compteFrom = Member_BanqueCompte::getCompteFromId($this->compteIdFrom);
		$compteTo = Member_banqueCompte::getCompteFromId($this->compteIdTo);
		
		/* On effectue la transaction que si les fonds sont suffisants dans le compte emetteur */
		if($this->value <= $compteFrom->getCash() || $compteFrom->getCash() == -1)
		{
			$compteFrom->changeCash('-', $this->value);
			$compteTo->changeCash('+', $this->value);
			/* todo : gérer avec une transaction pour éviter les problèmes */
			$compteFrom->setCash();
			$compteTo->setCash();
			/* Ajouter la transaction aux historiques */
			$compteFrom->add_bq_hist($compteTo->getNoBanque() . '-' . $compteTo->getNoCompte(), 'STRF', $this->value, 0);
			$compteTo->add_bq_hist($compteFrom->getNoBanque() . '-' . $compteFrom->getNoCompte(), 'RTRF', 0 ,$this->value);
		}
		
		/* Repousser le prochain transfert 1 mois plus tard */
		$this->date = strtotime("+1 month", $this->date);
		$this->setData();
	}
 }