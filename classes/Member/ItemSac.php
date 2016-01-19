<?php
/** Gestion des sacs à dos.
 *
 * @package Member
 * @subpackage Item
 */
class Member_ItemSac extends Member_Item
{
	private $capaciteMax;
	private $inventaire; //Tableau des items
	private $invPr; //Nombre de pr des items dans le sac
	
	function __construct (&$arr)
	{
		parent::__construct($arr);
		
		$this->capaciteMax		= $arr['db_capacite'];
	}
	
	
	/** Charge la liste des items en inventaire.
	 * <br> Selon le perso actuel (de l'objet perso), cette fonction créer/génère le 'tableau des item en inventaire'.
	 * <br> Cette fonction est apellée par getInventaire() et par les codes qui modifient l'inventaire (Inventaire, dépouillement, etc) dans le cas ou le 'tableau des item en inventaire' est innexistant.
	 *
	 */ 
	public function refreshInventaire()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		//Vider l'inventaire
		$this->inventaire = NULL;
		
		//Charger le nouvel inventaire
		$this->inventaire = Member_ItemFactory::createFromItemId($this->getInvId());
		
		$this->refreshPR();
	}
	

	
	
	/** Charge l'inventaire du personnage.
	 * <br > Fonctionne pour le personnage actuel. Retournera les informations sur les items.
	 *
	 * Exemple d'utilisation - Afficher des items de type badge
	 * <code>
	 * $i=0; $e=0;
	 * while( $item = $perso->getInventaire($i++))
	 *	if($item instanceof Member_ItemBadge)
	 *		$badgeEnPossessionDuPerso[$e++] = $item;
	 * </code>
	 * <br>
	 * 
	 * @param int $id Indexe de l'item en inventaire(dans le tableau) à retourner
	 * @return object Retourne un objet selon le type d'item dont il s'agit
	 */
	public function getInventaire($id)
	{ 
		//Vérifier si les items sont chargés
		if (empty($this->inventaire))
			$this->refreshInventaire();		
			
		//Vérifier si l'index de l'item demandé est hors de la liste existante
		if (!is_numeric($id) || $id>=count($this->inventaire))
			return false;
		
		return $this->inventaire[$id];
	}
	
	/** Calcule le nombre de PR des items contenu dans le sac
	 */
	private function refreshPR()
	{
		if($this->inventaire===NULL)
			$this->refreshInventaire();
		
		$this->invPr =0;
		foreach($this->inventaire as $item)
			$this->invPr += $item->getPr()*$item->getQte();
	}
	
	/** Retourne la capacité maximale (en PR) que peut contenir l'item
	 * @return int
	 */	
	public function getCapaciteMax() 	{ return $this->capaciteMax; }
	
	/** Retourne TRUE si l'item est actuellement équipé
	 * @return bool
	 */	
	public function isEquip()			{ return ($this->equip==1) ? true : false ; }
	
	/** Retourne TRUE si l'item est équipable, FALSE s'il n'est pas équipable
	 * @return bool
	 */
	public function canEquip()			{ return true; }
	
	/** Retourne le groupe affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroup()			{ return 'Sac à dos'; }
	
	/** Retourne le groupe (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroupTech()		{ return 'sac'; }
	
	/** Retourne le type affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getType()			{ return 'Sac à dos'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getTypeTech()		{ return 'sac'; }
	
	/** Retourne le nombre de PR des items contenu dans le sac
	 * @return int
	 */	
	public function getInvPr()
	{
		if(empty($this->invPr))
			$this->refreshPR();
		
		return $this->invPr;
	}
}


