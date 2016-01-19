<?php
/** Gestion des armes blanches.
 *
 * @package Member
 * @subpackage Item
 */
class Member_ItemArmeBlanche extends Member_ItemArme
{
	
	function __construct (&$arr)
	{
		parent::__construct($arr);
	}	
	
	
	/** Retourne le % de dommage sur l'arme
	 * @return int
	 */
	public function getPercDommage() 	{ return 100 - ($this->getResistance()	*100 / $this->getResistanceMax()); }
	
	/** Retourne le % de complexité de la réparation
	 * @return int
	 */
	public function getPercComplexite() 	{ return ($this->getForce() *100 / 12); }
	
	
	/** Retourne le type affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getType()			{ return 'Arme Blanche'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getTypeTech()		{ return 'arme_blanche'; }
	
	/** Retourne TRUE si l'item est équipable, FALSE s'il n'est pas équipable
	 * @return bool
	 */
	public function canEquip()			{ return true; }
}


