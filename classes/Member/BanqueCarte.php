<?php
/** Gestion des cartes de compte bancaire. (Gestion de l'ACCÈS, la carte(l'item), est liée à un accès. Supprimer l'accès rend la carte innefficace) 
 * 
 * Exemple d'utilisation - Charger un tableau de toutes les cartes qu'un personnage possède en inventaire:
 * <code>
 * $i=0;
 * while( $item = $perso->getInventaire($i++))
 * {
 * 		if($item instanceof Member_ItemCartebanque))
 * 		{
 * 			$query = 'SELECT *'
 * 					. ' FROM ' . DB_PREFIX . 'banque_cartes'
 * 					. ' WHERE carte_id=:carteId'
 *					. ' LIMIT 1;';
 * 			$prep = $db->prepare($query);
 *			$prep->bindValue(':carteId',	$item->getNoCarte(),	PDO::PARAM_INT);
 *			$prep->execute($db, __FILE__, __LINE__);
 *			$arr = $prep->fetch();
 *			$prep->closeCursor();
 *			$prep = NULL;
 *
 *			if ($arr === false)
 * 				die('Cette carte n\'est pas liée à un compte valide (' . $item->getNoCarte() . ')');
 * 		
 * 			$carteEnPossessionDuPerso[] = new Member_Banquecarte($arr);
 * 		}
 * }
 * </code>
 *
 * @package Member
 * @subpackage Banque
 */
class Member_BanqueCarte
{

	/** Numero de la carte (soit son ID).
	 * @var int
	 * @access private
	 */
	private $no;
	
	/** Inscription sur la carte.
	 * @var string
	 * @access private
	 */
	private $nom;
	
	/** NIP (Numéro d'identification Personnel)
	 * <br> mot de passe pour utiliser à la carte.
	 * @var int
	 * @access private
	 */
	private $nip;
	
	/** Indique si la carte est valide (activée)
	 * @var int
	 * @access private
	 */
	private $isValid;
	
	
	function __construct (&$arr)
	{
		$this->no		= $arr['carte_id'];
		$this->nom		= $arr['carte_nom'];
		$this->nip		= $arr['carte_nip'];
		$this->isValid	= $arr['carte_valid'];
	}
	
	
	/** Retourne le numéro de la carte (c'est aussi son ID)
	 * @return int
	 */
	public function getNo()					{ return $this->no; }
	
	/** Retourne l'inscription sur la carte (Le nom personnalisable de la carte)
	 * @return string
	 */
	public function getNom()				{ return $this->nom; }
	
	/** Retourne le NIP (Numéro d'Identification Personnel) requis pour utiliser la carte
	 * @return int
	 */
	public function getNip()				{ return $this->nip; }
	
	/** Retourne TRUE si la carte est activée
	 * @return bool
	 */
	public function isValid()				{ return ($this->isValid==1) ? true : false; }

	/** Change l'inscription sur la carte
	 * @param string $nom Nouvelle inscription
	 */
	public function changeNom($nom)			{ $this->nom = $nom; }
	
	/** Change le NIP
	 * @param int $nip Nouveau NIP
	 */
	public function changeNip($nip)			{ $this->nip = $nip; }
	
	/** Change le status de la carte
	 * @param bool $valid Nouveau status
	 */
	public function changeValid($valid)		{ $this->isValid = ($valid)? 1 : 0; }
	
	/** Enregistre les informations de la carte dans la base de donnée
	 */
	public function saveData()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
	
		$query = 'UPDATE ' . DB_PREFIX . 'banque_cartes'
					. ' SET carte_nom=:nom,'
						. ' carte_nip=:nip,'
						. ' carte_valid=:valid'
					. ' WHERE carte_id=:carte'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':nom',		$this->nom,		PDO::PARAM_STR);
		$prep->bindValue(':nip',		$this->nip,		PDO::PARAM_INT);
		$prep->bindValue(':valid',		$this->isValid,	PDO::PARAM_STR);
		$prep->bindValue(':carte',		$this->no,		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
}


