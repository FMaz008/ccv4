<?php
/** 
 * Gestion des liens entre les lieux. 
 * 
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package Member
 * @subpackage Lieu
 */
class Member_LieuLien extends Member_Lieu
{

	/**
	 * @access private
	 * @var int
	 */
	private $pa;
	
	/**
	 * @access private
	 * @var float
	 */
	private $cout;
	
	/**#@+
	 * @access private
	 * @var string
	 */
	private $protection;
	private $pass;
	/**
	 * URL
	 */
	private $icon;
	/**#@-*/
	
	/**
	 * @access private
	 * @var bool
	 */
	private $bloque;
	
	/**
	 * @access private
	 * @var array
	 */
	private $banId;
	
	
	
	/**
	 * Charge un lien en mémoire.
	 * 
	 * Chargera toutes les propriétés du lien en fonction d'un tableau.
	 * 
	 * @param array $arr  Tableau des propriétés du lien
	 */ 
	function __construct(&$arr)
	{
		$this->id			= $arr['lid'];
		$this->nom 			= stripslashes($arr['nom_affiche']);
		$this->nomTech 		= $arr['to'];
		$this->description	= stripslashes($arr['description']);
		
		$this->icon 		= $arr['icon'];
		$this->pa 			= $arr['pa'];
		$this->cout 		= $arr['cout'];
		$this->protection 	= $arr['protection'];
		$this->pass			= $arr['pass'];
		$this->bloque		= $arr['bloque'] == 1 ? true : false;
		
		$this->banId		= $arr['banid'];
	}
	
	/**
	 * Retourne le type de protection
	 *
	 * Cette fonction retourne 'pass', 'clef', 'ban' ou null
	 *
	 * @return string Nom technique
	 */
	public function getProtection()
	{
		if(!empty($this->banId))
			return 'ban';
		return $this->protection;
	}
	
	/**
	 * Retourne le nom affichable du type de protection
	 *
	 * Cette fonction retourne 'Digicode', 'Clé', Libre accès' ou 'Banni'
	 *
	 * @return string Nom affichable
	 */
	public function getProtectionTxt()
	{
		if(!empty($this->banId))
			return 'Banni';

		if(!$this->isAccessible())
			return 'Non-accessible';
		
		switch($this->protection)
		{
			case 'pass':	return 'Digicode (+1 PA)';	break;
			case 'clef':	return 'Clé (+1 PA)'; 		break;
			default:		return 'Libre accès';		break;
		}
	}
	
	/**
	 * Vérifie si le lieu est accessible.
	 * 
	 * Valide si perso est non bani et si le lien est non bloqué.
	 *
	 * @return bool
	 */
	public function isAccessible()
	{
		if (!empty($this->banId) || $this->bloque)
			return false;
		return true;
	}
	
	/** 
	 * Retourne le nom du fichier icone représentant le type de déplacement.
	 *
	 * Les types de déplacements peuvent être: autobus, secteur, etc.
	 *
	 * @return string
	 */
	public function getIcon()
	{
		return $this->icon;
	}
	
	/**
	 * Retourne le cout (Cr) du déplacement
	 *
	 * @return float
	 */
	public function getCout()
	{
		return $this->cout;
	}
	
	/**
	 * Retourne le nombre de PA requis pour le déplacement.
	 *
	 * @return int
	 */
	public function getPa()
	{
		return $this->pa;
	}
	
	/**
	 * Retourne le code CLEF ou DIGIPASS.
	 * 
	 * Les 2 type de protection utilise un code.
	 * La clé est un item contenant un code.
	 *
	 * @return int
	 */
	public function getPass()
	{
		return $this->pass;
	}
	
}

