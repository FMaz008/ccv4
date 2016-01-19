<?php
/** Gestion des armes lancées.
 *
 * @package Member
 * @subpackage Item
 */
class Member_ItemArmeLancee extends Member_ItemArme
{
	
	function __construct (&$arr)
	{
		parent::__construct($arr);
	}
	
	/** Retourne le type affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getType()			{ return 'Arme Lancée'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getTypeTech()		{ return 'arme_lancee'; }
	
	/** Retourne TRUE si l'item est équipable, FALSE s'il n'est pas équipable
	 * @return bool
	 */
	public function canEquip()			{ return true; }
}


