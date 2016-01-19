<?php
/** Gestion des mains nues (attaque avec les poings).
 *
 * @package Member
 * @subpackage Item
 */
class Member_ItemArmeMainsnues extends Member_ItemArme
{
	
	function __construct (){
		$arrSpec['inv_id']				= null;
		$arrSpec['db_nom']				= 'Mains nues';
		$arrSpec['inv_resistance']		= null;
		$arrSpec['db_regrouper']		= 1;
		$arrSpec['db_force']			= 1;
		$arrSpec['db_fiabilite']		= 100;
		$arrSpec['db_resistance']		= null;
		$arrSpec['inv_equip']			= null;
		$arrSpec['db_desc']				= 'Mains nues';
		$arrSpec['inv_extradesc']		= '';
		$arrSpec['db_portee']			= 'TC';
		$arrSpec['db_precision']		= 100;
		$arrSpec['db_description']		= null;
		$arrSpec['db_shock_pa']			= 0;	//Cout en PA de l'arme
		$arrSpec['db_img']				= null;
		$arrSpec['db_valeur']			= 0;
		$arrSpec['inv_qte']				= null;
		$arrSpec['db_id']				= null;
		$arrSpec['db_pr']				= null;
		$arrSpec['db_notemj']			= null;
		$arrSpec['inv_notemj']			= null;
		$arrSpec['inv_persoid']			= null;	
		$arrSpec['inv_lieutech']		= null;	
		$arrSpec['inv_boutiquelieutech']= null;
		$arrSpec['inv_boutiquePrixVente']= null;	
		$arrSpec['inv_boutiquePrixAchat']= null;
		$arrSpec['inv_cacheno']			= null;
		$arrSpec['inv_cachetaux']		= null;
		parent::__construct($arrSpec);
		
	}
	
	/** Retourne le type affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getType()			{ return 'Mains nues'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getTypeTech()		{ return 'mains_nues'; }
	
	/** Retourne TRUE si l'item est équipable, FALSE s'il n'est pas équipable
	 * @return bool
	 */
	public function canEquip()			{ return true; }
}


