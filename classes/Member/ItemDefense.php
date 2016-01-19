<?php
/** Gestion de base des items de défense.
 *
 * @package Member
 * @subpackage Item
 */
class Member_ItemDefense extends Member_Item
{
	protected $resistanceSeuil;
	
	
	function __construct (&$arr)
	{
		parent::__construct($arr);
		
		$this->resistanceSeuil	= $arr['db_seuilresistance'];
	}
	
	/**
	 * Seuil de résistance ( capacité d'absorbtion maximale par coup) de l'item.
	 *
	 * @return int Valeur en résistance
	 */
	public function getResistanceSeuil(){ return $this->resistanceSeuil; }

	/** Retourne le % de dommage sur l'arme
	 * @return int
	 */
	public function getPercDommage() 	{ return 100 - ($this->getResistance()	*100 / $this->getResistanceMax()); }
	
	/** Retourne le % de complexité de la réparation
	 * @return int
	 */
	public function getPercComplexite() 	{ return ($this->getResistanceSeuil() *100 / 9); }
	
	/** Retourne le groupe affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroup()			{ return 'Défense'; }
	
	/** Retourne le groupe (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroupTech()		{ return 'defense'; }
	
	/** Retourne TRUE si l'item est équipable, FALSE s'il n'est pas équipable
	 * @return bool
	 */
	public function canEquip()			{ return true; }
	
	

	
	/* Fonction qui calcule et absorbe des dommages en fonction de l'armure
	 * @param int $degats Quantité de dégat subit
	 * @param string $msgPerso variable contenant les message pour le personnage (sera mise à jour)
	 * @param string $msgVictime variable contenant les message pour la victime (sera mise à jour)
	 * @return int Total des dégats restants (total moins ceux qui ont été absorbés)
	 */
	public function absorbDamage($degats, &$msgPerso, &$msgVictime, $txtLoc='', $estUneRiposte=false)
	{
		
		//Calculer les dégats absorbé par l'armure
		if ($degats > $this->getResistanceSeuil()) //Les dégats sont plus grand que le seuil de l'armure
		{											// L'armure ne pourra absober que son seuil maximal.
			
			//Calculer ce que l'armure n'arrive pas à absorber
			$newRes = $this->getResistance() - $this->getResistanceSeuil();
			
			
			if($newRes <  0) 	//Il ne restait pas assez de résistance pour encaisser le seuil max
			{					//L'armure est morte
				
				//L'armure amorti tout ce qui lui reste
				$amorti = $this->getResistance();
				
				
				//L'armure à perdu toute sa résistance
				$this->setResistance(0);
				
				
				//Créer le message descriptif
				if($estUneRiposte==true)
				{
					$msgPerso	.= "\nVotre victime frappe sur une armure {$txtLoc} qui est rendue hors d'état ({$amorti} PV)";
					$msgVictime	.= "\nVous frappez sur votre une armure {$txtLoc} qui est maintenant hors d'état ({$amorti} PV)";
				}
				else
				{
					$msgPerso	.= "\nVous frappez sur une armure {$txtLoc} que vous rendez hors d'état ({$amorti} PV)";
					$msgVictime	.= "\nVotre aggresseur frappe sur votre armure {$txtLoc} qui est maintenant hors d'état ({$amorti} PV)";
				}
			}
			else //L'armure encaisse, absorbe son seuil max et résiste
			{
				
				//L'armure amorti tout ce qui lui reste
				$amorti = $this->getResistanceSeuil();
				
				
				//L'armure perd en résistance l'équivalent de ce qu'elle peut encaisser pour ce coup (le seuil)
				$this->changeResistance('-', $amorti);
				
				
				//Créer le message descriptif
				if($estUneRiposte==true)
				{
					$msgPerso	.= "\nVotre victime frappe sur une armure {$txtLoc} qui absorbe partiellement {$amorti} PV de dégats";
					$msgVictime	.= "\nVous frappez sur votre armure {$txtLoc} qui absorbe {$amorti} PV de dégats";
				}
				else
				{
					$msgPerso	.= "\nVous frappez sur une armure {$txtLoc} qui absorbe partiellement {$amorti} PV de dégats";
					$msgVictime	.= "\nVotre aggresseur frappe sur votre armure {$txtLoc} qui absorbe {$amorti} PV de dégats";
				}
			}
			
		}
		else //Les dégats sont inférieur au seuil de l'armure
		{
			
			//Calculer ce que l'armure n'arrive pas à absorber
			$newRes = $this->getResistance() - $degats;
			
			
			if ($newRes < 0) //Il ne restait pas assez de résistance pour encaisser le dommage (qui est sous le seuil)
			{				//L'armure est morte
				
				//L'armure amorti tout ce qui lui reste
				$amorti = $this->getResistance();
				
				
				//L'armure à perdu toute sa résistance
				$this->setResistance(0);
				
				
				//Créer le message descriptif
				$msgPerso	.= "\nVous frappez sur une armure {$txtLoc} de que vous rendez hors d'état ({$amorti} PV)";
				$msgVictime	.= "\nVotre aggresseur frappe sur votre armure {$txtLoc} qui est maintenant hors d'état ({$amorti} PV)";
			}
			else	//L'armure encaisse, absorbe tout les dégats et résiste
			{
				
				//L'armure amorti tout ce qui lui reste
				$amorti = $degats;
				
				
				//L'armure perd en résistance l'équivalent de ce qu'elle peut encaisser pour ce coup (le seuil)
				$this->changeResistance('-', $amorti);
				
				
				//Créer le message descriptif
				$msgPerso	.= "\nVous frappez sur une armure {$txtLoc} qui absorbe les dégats ({$amorti} PV)";
				$msgVictime	.= "\nVotre aggresseur frappe sur votre armure {$txtLoc} qui absorbe les dégats ({$amorti} PV)";
			}
		}
		
		
		//Calculer les dégats après l'impact sur l'armure
		$degats -= $amorti;
				
		return $degats;
	}
	
	
}


