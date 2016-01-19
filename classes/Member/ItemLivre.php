<?php
/** Gestion des livres.
 *
 * @package Member
 * @subpackage Item
 */
class Member_ItemLivre extends Member_Item
{

	private $titre;
	private $auteur;
	private $contenu;
	
	function __construct(&$arr)
	{
		$arr['inv_notemj']				= (isset($arr['inv_notemj'])) ? $arr['inv_notemj'] : null;
		$arr['inv_qte']					= (isset($arr['inv_qte'])) ? $arr['inv_qte'] : null;
		$arr['inv_id']					= (isset($arr['inv_id'])) ? $arr['inv_id'] : null;
		$arr['inv_persoid']				= (isset($arr['inv_persoid'])) ? $arr['inv_persoid'] : null;
		$arr['inv_lieutech']			= (isset($arr['inv_lieutech'])) ? $arr['inv_lieutech'] : null;
		$arr['inv_boutiquelieutech']	= (isset($arr['inv_boutiquelieutech'])) ? $arr['inv_boutiquelieutech'] : null;
		$arr['inv_boutiquePrixVente']	= (isset($arr['inv_boutiquePrixVente'])) ? $arr['inv_boutiquePrixVente'] : null;
		$arr['inv_boutiquePrixAchat']	= (isset($arr['inv_boutiquePrixAchat'])) ? $arr['inv_boutiquePrixAchat'] : null;
		
		parent::__construct($arr);
		
		$this->titre			= stripslashes($arr['db_nom']);
		$this->auteur			= stripslashes($arr['db_pass']);
		$this->contenu			= stripslashes($arr['db_param']);
	}
	
	
	/** Retourne le titre du livre
	 * @return string
	 */	
	public function getTitre()			{ return $this->titre; }
	
	/** Retourne ll'auteur du livre
	 * @return int
	 */	
	public function getAuteur()			{ return $this->auteur; }
	
	/** Retourne le contenu du livre (parfait pour un textarea)
	 * @return int
	 */	
	public function getContenu()		{ return $this->contenu; }
	
	/** Retourne le contenu du livre avec les saut de ligne en <br> (insertion direct dans une page)
	 * @return int
	 */	
	public function getContenuHTML()	{ return BBCodes($this->contenu, false, true, true); }
	
	/** Retourne la taille du contenu du livre
	 * @return float
	 */
	public function getTaille()			{ return round(strlen($this->contenu)/1024,2); }
	
	/** Retourne le groupe affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroup()			{ return 'Livre'; }
	
	/** Retourne le groupe (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroupTech()		{ return 'livre'; }
	
	/** Retourne le type affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getType()			{ return 'Livre'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	

	public function getTypeTech()		{ return 'livre'; }
}


