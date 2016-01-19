<?php
/** Gestion des trousses de soin
 *
 * @package Member
 * @subpackage Item
 */
class Member_ItemTrousse extends Member_Item
{
	
	function __construct(&$arr)
	{
		parent::__construct($arr);
	}
	
	/** Retourne le groupe affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroup()			{ return 'Médecine'; }
	
	/** Retourne le groupe (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroupTech()		{ return 'medecine'; }
	
	/** Retourne le type affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getType()			{ return 'Trousse'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */
	public function getTypeTech()		{ return 'trousse'; }
}

