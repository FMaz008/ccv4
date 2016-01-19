<?php
/** Gestion des banque. 
 * <br> NOTE: Généralement (quoi que ca ne poserais aucun problème), un seul objet banque est instancié à la fois
 *
 * Exemple d'utilisation - Charger une banque
 * <code>
 * $query = 'SELECT *'
 * 			. ' FROM ' . DB_PREFIX . 'banque'
 * 			. ' WHERE banque_no=:banqueNo'
 *			. ' LIMIT 1;';
 *	$prep = $db->prepare($query);
 *	$prep->bindValue(':banqueNo',	$_POST['banque'],	PDO::PARAM_INT);
 *	$prep->execute($db, __FILE__, __LINE__);
 *	$arr = $prep->fetch();
 *	$prep->closeCursor();
 *	$prep = NULL;
 *
 * if ($arr === false)
 * 	die('Cette banque n\'existe pas.');
 *
 * $banque = new Member_Banque($arr);
 * </code>
 *
 * @package Member
 * @subpackage Banque
 */
class Member_Banque
{
	
	/** Id dans la table banque.
	 * @var int
	 * @access private
	 */
	private $id;
	
	/** Nom technique du lieu contant la banque.
	 * @var string
	 * @access private
	 */
	private $lieuTech;
	
	/** Numero de la banque (pas son ID)
	 * @var int
	 * @access private
	 */
	private $noBanque;
	
	/** Nom de la banque
	 * @var string
	 * @access private
	 */
	private $nom;
	
	/** Si la banque supporte les retraits
	 * @var int
	 * @access private
	 */
	private $retrait;
	
	/** Frais d'ouverture d'un compte à cette banque
	 * @var float
	 * @access private
	 */
	private $fraisOuverture;
	
	/** Si la banque authorise le débit direct via des téléphone
	 * @var string
	 * @access private
	 */
	private $telephone;
	
	/** Tableau d'objet Compte. Contiend des comptes associé à cette banque.
	 * @var array
	 * @access private
	 */
	private $arrCompte;
	
	
	
	function __construct (&$arr)
	{
		$this->id 					= $arr['banque_id'];
		$this->lieuTech				= $arr['banque_lieu'];
		$this->noBanque				= $arr['banque_no'];
		$this->nom					= stripslashes($arr['banque_nom']);
		$this->retrait				= $arr['banque_retrait'];
		$this->fraisOuverture		= $arr['banque_frais_ouverture'];
		$this->telephone			= $arr['banque_telephone'];
	}
	
	
	/** Retourne un compte appartenant la banque
	 * <br> Si le compte n'existe pas, la méthode va essayer de l'instancier
	 * <br> et de l'associer à la banque de l'objet courrant.
	 * <br> NOTE: La fonction NE VALIDE PAS si le compte appartiend bel et bien à cette banque.
	 * @return object retourne un objet de type Member_Banquecompte
	 */
	public function getCompte($compteNo, $arr=null)
	{
		
		if (!empty($arrCompte))
			foreach($this->arrCompte as $compte)
				if($compte->getNo() == $compteNo)
					return $carte;
		
		
		//Non trouvé, charger le compte
		if (empty($arr))
		{
			$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
			$db = $dbMgr->getConn('game'); //Demander la connexion existante
			
			$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'banque_comptes'
					. ' WHERE compte_compte=:compteNo'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':compteNo',		$compteNo,	PDO::PARAM_STR);
			$prep->execute($db, __FILE__,__LINE__);
			$arr = $prep->fetch();
			
			if ($arr === false)
				die('Le compte demandé n\'existe pas dans la base de donnée');
			
		}
		
		$compte = new Member_BanqueCompte($arr);
		$this->arrCompte[] = $compte;
		return $compte;
	}
	
	
	
	
	
	/** Retourne l'id dela banque.
	 * @return int
	 */
	public function getId()					{ return $this->id; }
	
	/** Retourne le nom technique du lieu contant la banque.
	 * @return string
	 */
	public function getLieuTech()			{ return $this->lieuTech; }
	
	/** Retourne le numéro de la banque (pas son ID).
	 * @return int
	 */
	public function getNoBanque()			{ return $this->noBanque; }
	
	/** Retourne le nom de la banque
	 * @return string
	 */
	public function getNom()				{ return $this->nom; }
	
	/** Retourne si la banque supporte les retraits
	 * @return bool
	 */
	public function canRetrait() 			{ return ($this->retrait==1) ? true : false; }
	
	/** Retourne le frais d'ouverture d'un nouveau compte à cette banque
	 * @return float
	 */
	public function getFraisOuverture()		{ return $this->fraisOuverture; }
	
	/** Retourne si la banque supporte les retraits via des téléphones
	 * @return bool
	 */
	public function getSupportTelephone()	{ return ($this->telephone==1) ? true : false; }

	
}


