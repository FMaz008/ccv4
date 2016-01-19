<?php
/** Gestion de base appliquable à tout les items du jeu de type principal "arme".
 *
 * @package Member
 * @subpackage Item
 */
abstract class Member_ItemArme extends Member_Item
{

	/** Force globale de l'arme.
	 * <br> Facteur principal déterminant le maximum de dommage qu'une arme peut engendrer.
	 * @var int
	 * @access protected
	 */
	protected $force;
	
	/** Fiabilité de l'arme.
	 * <br> Facteur principal déterminant si l'arme fonctionnera correctement ou pas lors d'une utilisation (Ex.: Enrayement = non-fiabilitée).
	 * @var int
	 * @access protected
	 */
	protected $fiabilite;
	
	
	/** Portée effective de l'arme.
	 * <br> Détermine la portée maximale à laquelle l'arme peut-être utilisée.
	 * <br> Valeur possible (comme pour la taille d'un lieu): 'TC', 'C', 'M', 'L', 'TL'
	 * @var string
	 * @access protected
	 */
	protected $portee;
				
	/** Précision de l'arme.
	 * <br> Facteur principal déterminant si l'arme atteidra correstement la cible visée.
	 * @var int
	 * @access protected
	 */
	protected $precision;
	
	/** Cout en PA de l'arme.
	 * <br> Facteur principal déterminant le cout par attaque.
	 * @var int
	 * @access protected
	 */
	protected $pa;
	
	
	function __construct (&$arr)
	{
		parent::__construct($arr);
		
		$this->force			= $arr['db_force'];
		$this->fiabilite		= $arr['db_fiabilite'];
		$this->portee			= $arr['db_portee'];
		$this->precision		= $arr['db_precision'];
		$this->pa				= $arr['db_shock_pa'];
	}
	
		
	/** Retourne le niveau de force de l'arme.
	 * @return int
	 */
	public function getForce() 			{ return $this->force; }
	
	/** Retourne le niveau de fiabilité de l'arme.
	 * @return int
	 */
	public function getFiabilite()		{ return $this->fiabilite; }
	
	/** Retourne la portée minimale de l'arme.
	 * @return string
	 */
	public function getPortee()			{ return $this->portee; }
	
	/** Retourne le niveau de précision de l'arme.
	 * @return int
	 */	
	public function getPrecision()		{ return $this->precision; }

	/** Retourne le cout en PA de l'Arme par tour d'attaque
	 * @return int
	 */	
	public function getPa()				{ return $this->pa; }
	
	/** Retourne le groupe affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroup()			{ return 'Arme'; }
	
	/** Retourne le groupe (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroupTech()		{ return 'arme'; }
	
}


