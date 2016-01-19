<?php
/** Gestion des armes explosives.
 *
 * @package Member
 * @subpackage Item
 */
class Member_ItemArmeExplosive extends Member_ItemArme
{
	
	function __construct (&$arr)
	{
		parent::__construct($arr);
	}
	
	/** Retourne le type affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getType()			{ return 'Arme Explosive'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getTypeTech()		{ return 'arme_explosif'; }
}


