<?php
/** Gestion des cartes bancaires.
 *
 * @package Member
 * @subpackage Item
 */
class Member_ItemCartebanque extends Member_Item
{
	
	private $noCarte; 
	
	private $note;
	
	function __construct (&$arr)
	{
		parent::__construct($arr);
		
		$this->noBanque			= $arr['inv_nobanque'];
		$this->noCompte			= $arr['inv_nocompte'];
		$this->noCarte			= $arr['inv_nocarte'];
		$this->note				= $arr['inv_param'];
	}
	

	/** Retourne l'ID de la carte
	 * @return int
	 */	
	public function getNoCarte()		{ return $this->noCarte; }

	/** Retourne le no de la banque au format XXXX
	 * @return int
	 */	
	public function getNoBanque()		{ return $this->noBanque; }
	
	/** Retourne le no du compte au format XXXX-XXXX-XXXX
	 * @return int
	 */	
	public function getNoCompte()		{ return $this->noCompte; }
	
	
	public function getDescription()
	{
		$desc = parent::getDescription();
		$desc .= '<br /><br />Carte #:';
		$desc .= $this->getNoBanque() . '-';
		$desc .= $this->getNoCompte() . '-';
		$desc .= $this->getNoCarte();
		return $desc;
		
	}
	/** Retourne la note inscrite sur la carte (généralement le # du compte)
	 * @return string
	 */	
	public function getNote()			{ return $this->note; }
	
	/** Retourne le groupe affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroup()			{ return 'Carte'; }
	
	/** Retourne le groupe (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroupTech()		{ return 'carte'; }
	
	/** Retourne le type affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getType()			{ return 'Carte Bancaire'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getTypeTech()		{ return 'cartebancaire'; }
}


