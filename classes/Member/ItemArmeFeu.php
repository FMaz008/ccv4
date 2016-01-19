<?php
/** Gestion des armes à feu.
 *
 * @package Member
 * @subpackage Item
 */
class Member_ItemArmeFeu extends Member_ItemArme
{
	private $tirParTour; 
	private $munitionMax; 
	private $munition; 
	
	function __construct (&$arr)
	{
		parent::__construct($arr);
		
		$this->tirParTour		= $arr['db_tir_par_tour'];
		$this->munitionMax		= $arr['db_capacite'];
		$this->munition			= $arr['inv_munition'];
	}
	
	
	/** Retourne le % de dommage sur l'arme
	 * @return int
	 */
	public function getPercDommage() 	{ return 100 - ($this->getResistance()	*100 / $this->getResistanceMax()); }
	
	/** Retourne le % de complexité de la réparation
	 * @return int
	 */
	public function getPercComplexite() 	{ return ($this->getForce() *100 / 12); }
	
	/** Retourne le nombre de tir(s) par tour d'attaque
	 * @return int
	 */	
	public function getTirParTour() 	{ return $this->tirParTour; }
	
	/** Retourne le nombre de munition que l'arme peut contenir à pleine capacité
	 * @return int
	 */	
	public function getMunitionMax()	{ return $this->munitionMax; }
	
	/** Retourne le nombre de munition que l'arme contiend actuellement
	 * @return int
	 */	
	public function getMunition()		{ return $this->munition; }
	
	/** utilise un nombre de munitions
	 * <br> Cette méthode calcule juste le nombre de munitions restantes et met a jour l'objet.
	 * <br> IMPORTANT: pour sauvegarder en DB les modifications, vous DEVEZ apeller {@link setMunition()}.
	 * @param float $nbrPa Nombre de munitions à retirer
	 */
	public function useMunition($nbrMunition)
	{
		$this->munition-=$nbrMunition;
	}

	/** Met à jour le nombre de munition que contiend l'arme
	 */	
	public function setMunition($qte)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if ($qte<=$this->getMunitionMax())
		{
			$this->munition = $qte;
			$query = 'UPDATE ' . DB_PREFIX .'item_inv'
					. ' SET inv_munition=:qte'
					. ' WHERE inv_id=:id'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':qte',	$qte,				PDO::PARAM_INT);
			$prep->bindValue(':id',		$this->getInvId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
	}
	
	/** Retourne le type affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getType()			{ return 'Arme à Feu'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getTypeTech()		{ return 'arme_feu'; }
	
	/** Retourne TRUE si l'item est équipable, FALSE s'il n'est pas équipable
	 * @return bool
	 */
	public function canEquip()			{ return true; }
}


