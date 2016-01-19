<?php
/** Gestion des clés.
 *
 * @package Member
 * @subpackage Item
 */
class Member_ItemBadge extends Member_Item
{
	
	private $code;
	
	protected $nom;
	
	private $notemj;
	
	private $titre;
	
	private $contenu;
	
	
	function __construct (&$arr)
	{
		parent::__construct($arr);
		
		$this->nom				= $this->nom . ' - ' . $arr['inv_param'];
		$this->notemj			= $arr['inv_notemj'];
		$this->titre			= $arr['inv_param'];
		$this->contenu			= $arr['inv_memoiretext'];
	}
	

	/** Retourne le titre de la carte
	 * @return string
	 */	
	public function getTitre()		{ return $this->titre; }

	/** Retourne le contenu de la carte
	 * @return string
	 */	
	public function getContenu()		{ return $this->contenu; }
	
	/** Retourne l'inscription burinée sur la clé
	 * @return string
	 */	
	public function getNom()		{ return $this->nom; }
	
	/** Retourne la note MJ
	 * @return string
	 */	
	public function getNoteMJ()		{ return $this->notemj; }
	
	/** Retourne le groupe affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroup()		{ return 'Badge'; }
	
	/** Retourne le groupe (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroupTech()	{ return 'badge'; }
	
	/** Retourne le type affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getType()		{ return 'Badge'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getTypeTech()	{ return 'badge'; }
}


