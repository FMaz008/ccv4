<?php
/** Gestion des casier. 
 * 
 * Exemple d'utilisation:
 * <code>
 * $query = 'SELECT * FROM ' . DB_PREFIX . 'lieu_casier WHERE id=:casierId;';
 * 	$prep = $db->prepare($query);
 *	$prep->bindValue(':casierId',	345,	PDO::PARAM_INT);
 *	$prep->execute($db, __FILE__, __LINE__);
 *	$arr = $prep->fetch();
 *	$prep->closeCursor();
 *	$prep = NULL;
 *
 * $casier = new Member_Casier($arr);
 * echo "Le casier 345 est dans le lieu: " . $casier->getNomTech();
 * </code>
 *
 * @package Member
 * @subpackage Casier
 */
class Member_Casier
{
	
	/** Id du casier
	 * @var int
	 * @access private
	 */
	private $id;
	
	/** Nom inscrit sur le casier
	 * @var sting
	 * @access private
	 */	
	private $nom;
	
	/** Lieu Technique dans lequel est situé le casier
	 * @var sting
	 * @access private
	 */	
	private $lieuId;
	
	/** Capacité du casier (en PR)
	 * @var int
	 * @access private
	 */	
	private $capacite;
	
	/** Résistance du casier (Comparable à des "Pv")
	 * @var int
	 * @access private
	 */	
	private $resistance;
	
	/** Array des items contenu dans le casier
	 * @var array
	 * @access private
	 */	
	private $items;		//Items contenu dans le casier
	
	/** Type de protection du casier (null, "clef", "pass")
	 * @var sting
	 * @access private
	 */	
	private $protection;
	
	/** Mot de passe ou code de clé qui protège le casier
	 * @var sting
	 * @access private
	 */	
	private $pass;
	
	
	
	
	/** Charge un casier en mémoire.
	 * <br> Chargera toutes les propriétés d'un casier en fonction du résultat d'un select * sur la table lieu_casier.
	 * 
	 * @param array &$arr	array d'information sur le casier à charger.
	 */ 
	function __construct(&$arr)
	{
		//Charger les informations générale du casier en mémoire
		$this->id 			= $arr['id_casier'];
		$this->nom 			= stripslashes($arr['nom_casier']);
		$this->lieuId 		= $arr['lieuId'];
		$this->capacite		= stripslashes($arr['capacite_casier']);
		$this->resistance	= $arr['resistance_casier'];
		$this->protection	= $arr['protection_casier'];
		$this->pass			= $arr['pass_casier'];
		$this->items	 	= array();
	}
	

	
	
	
	/** Charge la liste des items en inventaire.
	 * <br> Selon le lieu actuel (de l'objet lieu), cette fonction créer/génère le 'tableau des item en inventaire'.
	 * <br> Cette fonction est apellée par {@link getItems()}
	 *
	 * @param int $casier  Si défini, sert à spécifier de quel casier ont souhaite obtenir l'inventaire
	 */ 
	public function refreshItems()
	{
		//Vider l'inventaire
		$this->items = NULL;

		//Charger le nouvel inventaire
		$this->items = Member_ItemFactory::createFromCasierId($this->getId());
	}
	

	
	
	/** Charge l'inventaire du lieu.
	 * <br > Fonctionne pour le lieu actuel. Retournera les informations sur les items.
	 *
	 * Exemple d'utilisation - Afficher des items de type badge du casier 345
	 * <code>
	 * $i=0; $e=0; $f=0;
	 * while( $casier = $lieu->getCasiers($i++))
	 *	if($casier->getId() == 345)
	 *		while($item = $casier->getItems($e++))
	 *			if($item instanceof Member_ItemBadge)
	 *				$badgeDansLeCasier[$f++] = $item;
	 * </code>
	 * <br>
	 * 
	 * @param int $id		Indexe de l'item en inventaire(dans le tableau) à retourner.
	 * @return object		Retourne un objet selon le type d'item dont il s'agit.
	 */
	public function getInventaire($id)
	{
		//Vérifier si les items sont chargés
		if (empty($this->items))
			$this->refreshItems();
			
		//Vérifier si l'index de l'item demandé est hors de la liste existante
		if (!is_numeric($id) || $id>=count($this->items))
			return false;
		
		return $this->items[$id];
	}
	
	
	
	
	
	/** Retourne l'ID du casier
	 * @return int
	 */
	public function getId()					{	return $this->id; }
	
	/** Retourne le nom technique du lieu dans lequel est situé le casier.
	 * @return string
	 */
	public function getLieuId()				{	return $this->lieuTech; }
	
	/** Retourne le nom inscrit sur le casier.
	 * @return string
	 */
	public function getNom()				{	return $this->nom; }
	
	
	/** Retourne la capacité du casier
	 * @return int
	 */
	public function getCapacite()			{	return $this->capacite; }
	
	/** Retourne la resistance du casier
	 * @return int
	 */
	public function getResistance()			{	return $this->resistance; }
	
	/** Retourne le pass de la protection du casier
	 * @return string
	 */
	public function getPass()				{	return $this->pass; } 
	
	/** Retourne la protection technique du casier
	 * @return string
	 */
	public function getProtection()			{	return $this->protection; }
	
	/** Retourne la protection affichable du casier
	 * @return string
	 */
	public function getProtectionTxt()
	{
		switch($this->protection)
		{
			case 'clef':	return 'Clé';		break;
			case 'pass':	return 'Code';		break;
			default:		return 'Aucune';	break;
		}
	}
	
	/** Retourne la capacité utilisée du casier (le cumul des PR des items contenus dans le casier)
	 * @return int
	 */
	public function getPr()
	{
		//Vérifier si les items sont chargés
		if (empty($this->items))
			$this->refreshItems();
		
		//Calculer les PR de chaque item dans la liste
		$pr=0;
		if(count($this->items)>0)
			foreach($this->items as $item)
				$pr+=$item->getPr();
		
		return $pr;
	}
}





