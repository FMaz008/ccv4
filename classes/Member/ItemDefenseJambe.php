<?php
/** Gestion des items de défense pour les jambes.
 *
 * @package Member
 * @subpackage Item
 */
class Member_ItemDefenseJambe extends Member_ItemDefense
{

	function __construct (&$arr)
	{
		parent::__construct($arr);
	}
	
	
	/** Retourne le type affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getType()			{ return 'Défense Jambe'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getTypeTech()		{ return 'defense_jambe'; }
}


