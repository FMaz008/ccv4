<?php
/** Gestion des casino. 
 *
 * Exemple d'utilisation - Charger une casino
 * <code>
 * $query = 'SELECT *'
 * 			. ' FROM ' . DB_PREFIX . 'casino'
 * 			. ' WHERE casino_id=:casinoId'
 *			. ' LIMIT 1;';
 * 	$prep = $db->prepare($query);
 *	$prep->bindValue(':casinoId',	$id,	PDO::PARAM_INT);
 *	$prep->execute($db, __FILE__, __LINE__);
 *	$arr = $prep->fetch();
 *	$prep->closeCursor();
 *	$prep = NULL;
 *
 *	if ($arr === false)
 * 		die('Ce casino n\'existe pas.');
 *
 * $banque = new Member_Banque($arr);
 * </code>
 *
 * @package Member
 * @subpackage Casino
 */
class Member_Casino
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
	
	/** Nom de la banque
	 * @var string
	 * @access private
	 */
	private $nom;
	
	
	/** Cash du casino
	 * @var int
	 * @access private
	 */
	private $cash;
	
	
	
	function __construct (&$arr)
	{
		$this->id 					= $arr['casino_id'];
		$this->lieuTech				= $arr['casino_lieu'];
		$this->nom					= stripslashes($arr['casino_nom']);
		$this->cash					= $arr['casino_cash'];
	}
	
	
	
	
	
	/** Retourne l'id du casino.
	 * @return int
	 */
	public function getId()					{ return $this->id; }
	
	/** Retourne le nom technique du lieu contant le casino.
	 * @return string
	 */
	public function getLieuTech()			{ return $this->lieuTech; }
	
	
	/** Retourne le nom du casino
	 * @return string
	 */
	public function getNom()				{ return $this->nom; }
	
	
	/** Retourne le cash du casino
	 * @return bool
	 */
	public function getCash()				{ return $this->cash; }

	
	/** Modifier le solde du compte.
	 * <br> Cette méthode s'occupe de calculer elle même le solde.
	 * <br> IMPORTANT: pour sauvegarder en DB les modifications, vous DEVEZ apeller {@link setCash()}.
	 * @param char $plusMoins Prend la valeur '+' ou '-' selon si on désire ajouter ou retirer de l'argent
	 * @param float $montant Montant d'argent  à ajouter ou retirer
	 */
	public function changeCash($plusMoins, $montant)
	{
		if($plusMoins=='+')
			$this->cash+=$montant;
		elseif($plusMoins=='-')
			$this->cash-=$montant;
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
		
		$query = 'UPDATE ' . DB_PREFIX . 'casino'
				. ' SET casino_cash=:cash'
				. ' WHERE casino_id=:id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':cash',	$this->cash,	PDO::PARAM_INT);
		$prep->bindValue(':id',		$this->id,		PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		
		
		$this->cash = $montant;
	}
}


