<?php
/** Gestion des livres.
 *
 * @package Member
 * @subpackage Item
 */
class Member_ItemAutre extends Member_Item
{

	
	function __construct(&$arr)
	{
		parent::__construct($arr);
	}
	
	
	
	/** Retourne le groupe affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroup()			{ return 'Autre'; }
	
	/** Retourne le groupe (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroupTech()		{ return 'autre'; }
	
	/** Retourne le type affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getType()			{ return 'Autre'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	

	public function getTypeTech()		{ return 'autre'; }
}


