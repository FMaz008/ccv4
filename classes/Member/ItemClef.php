<?php
/** Gestion des clés.
 *
 * @package Member
 * @subpackage Item
 */
class Member_ItemClef extends Member_Item
{
	
	private $code;
	
	protected $nom;
	
	private $notemj;
	
	function __construct (&$arr)
	{
		parent::__construct($arr);
		
		$this->code			= $arr['inv_memoiretext'];
		if(!empty($arr['inv_param']))
			$this->nom		.= '- ' . $arr['inv_param'];
		$this->notemj		= $arr['inv_notemj'];
	}
	

	/** Retourne le 'code de serrure'
	 * @return string
	 */	
	public function getCode()		{ return $this->code; }

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
	public function getGroup()		{ return 'Clé'; }
	
	/** Retourne le groupe (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroupTech()	{ return 'clef'; }
	
	/** Retourne le type affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getType()		{ return 'Clé'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getTypeTech()	{ return 'clef'; }
}


