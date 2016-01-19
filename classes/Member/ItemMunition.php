<?php
/** Gestion des munitions.
 *
 * @package Member
 * @subpackage Item
 */
class Member_ItemMunition extends Member_Item
{

	function __construct (&$arr)
	{
		parent::__construct($arr);
		
	}
	
	
	/** Retourne le groupe affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroup()			{ return 'Consommable'; }
	
	/** Retourne le groupe (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroupTech()		{ return 'consommable'; }
	
	/** Retourne le type affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getType()			{ return 'Munitions'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getTypeTech()		{ return 'munition'; }
}


