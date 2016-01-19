<?php
/** Gestion des items de défense pour la tête.
 *
 * @package Member
 * @subpackage Item
 */
class Member_ItemDefenseTete extends Member_ItemDefense
{
	private $masque;
	
	function __construct (&$arr)
	{
		parent::__construct($arr);
		
		$this->masque			= $arr['db_masque'];
	}
	
	public function getMasque() 		{ return ($this->masque==1) ? true : false; }
	
	
	/** Retourne le type affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getType()			{ return 'Défense Tête'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getTypeTech()		{ return 'defense_tete'; }
}


