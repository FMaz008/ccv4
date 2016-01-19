<?php
/** Gestion des drogues (drogues produite à partir de substances)
 *
 * @package Member
 * @subpackage Item
 */
class Member_ItemDrogueDrogue extends Member_ItemDrogue
{
		
	function __construct (&$arr)
	{
		parent::__construct($arr);
		
		$this->duree			= $arr['inv_duree'];
		$this->boostPa			= $arr['inv_boost_pa'];
		$this->shockPa			= $arr['inv_shock_pa'];
		$this->boostPv			= $arr['inv_boost_pv'];
		$this->shockPv			= $arr['inv_shock_pv'];
		$this->percStatAgi		= $arr['inv_perc_stat_agi'];
		$this->percStatDex		= $arr['inv_perc_stat_dex'];
		$this->percStatPer		= $arr['inv_perc_stat_per'];
		$this->percStatFor		= $arr['inv_perc_stat_for'];
		$this->percStatInt		= $arr['inv_perc_stat_int'];
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
	public function getType()			{ return 'Drogue'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getTypeTech()		{ return 'drogue_drogue'; }
	
}


