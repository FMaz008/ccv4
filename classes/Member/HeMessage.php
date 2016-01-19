<?php
/** Gestion d'un message de l'Historique des Évènements.
 * 
 * Exemple d'utilisation:
 * <code>
 * $msg = new Member_HeMessage();
 * </code>
 *
 * @package Member
 * @subpackage HE
 */
class Member_HeMessage
{

	private $msg;
	
	private $from;
	
	private $to;
	
	private $date;
	
	private $id;
	
	private $style;
	
	private $type;
	
	function __construct(&$perso, &$arr, $bbcode=true)
	{
		
		$this->msg = stripslashes($arr['msg']);

		//Par défaut, interpréter le BBCode
		if($bbcode)
			$this->msg = BBCodes($this->msg);
		
		/*
		$this->date = mktime(	date("H", $arr['date']),
								date("i", $arr['date']),
								date("s", $arr['date']),
								date("d", $arr['date']),
								date("m", $arr['date']),
								date("Y", $arr['date'])
							);
		*/
		$this->date = $arr['date'];
		$this->id = $arr['hid'];
		$this->style = (isset($arr['style'])) ? $arr['style'] : '';
		$this->type = $arr['type'];
		$this->from = array();
		$this->to = array();
		
		//Ajouter les FROM/TO
		$this->addFromTo($perso, $arr);
	}
	
	
	/** Fonction qui prépare l'affichage des destinataire et récepteur d'un message dans le HE
	 *
	 * @param Member_Perso &$perso Instance du personnage actuellement joué (ou autre pour la consultation des HE de lieux)
	 * @param array &$arr Tableau des résultat de la requête de HE
	 */
	public function addFromTo(&$perso, &$arr)
	{
		//$i = count($this->{$arr['fromto']});
		$tmp = array();
		
		if ($arr['persoid'] > 0)
		{
			if($perso instanceof Member_Perso)
			{
				if($arr['persoid']===0) //  Message du système
				{
					$tmp['id'] 		= '';
					$tmp['nom']		= 'Système';
					$tmp['sexe']	= 's';
				}
				else //Message d'un joueur
				{
					if((int)$arr['persoid']===$perso->getId())
					{
						//Le joueur est le perso actuellement joué
						$tmp['id'] 			= $arr['persoid'];
						$tmp['sexe'] 		= $arr['sexe'];
						
						//son nom est forcément connu: c'est nous.
						$tmp['nom'] 		= $perso->getNom();
						
					}
					//elseif($arr['show']!==HE_UNIQUEMENT_MOI) //Valider si on peut afficher cette personne
					elseif($arr['show'] == HE_TOUS || $arr['fromto'] == 'from') //Valider si on peut afficher cette personne
					{
						//Informations générales
						$tmp['id'] 			= $arr['persoid'];
						$tmp['sexe'] 		= $arr['sexe'];
						
						if(empty($arr['nom']))
						{
							//Joueur inconnu (non-nommé)
							$tmp['nom'] 	= ($arr['sexe']=='f') ? 'Inconnue' : 'Inconnu';
						}
						else
						{
							//Joueur connu
							$tmp['nom']		= stripslashes($arr['nom']);
						}
					}
				}
			}
			else // Le paramètre perso passe uniquement un ID (HE des lieux des MJ)
			{
				$tmp['id']			= stripslashes($arr['persoid']);
				$tmp['nom']			= stripslashes($arr['nom']);
				$tmp['sexe']		= stripslashes($arr['sexe']);
			}
		}
		else  // 0 -> aller chercher dans completmeent (systeme, etc ...)
		{
			$tmp['id'] 			= stripslashes($arr['persoid']);
			$tmp['nom']			= stripslashes($arr['name_complement']);
			$tmp['sexe']		= '';
		}
		if(!empty($tmp))
			$this->{$arr['fromto']}[] = $tmp;
	}
	
	public function getId()			{ return $this->id; }
	public function getDate()		{ return $this->date; }
	public function getDateTxt()	{ return fctToGameTime($this->date, true); }
	public function getMsg()		{ return $this->msg; }
	public function getFrom()		{ return $this->from; }
	public function getTo()			{ return $this->to; }
	public function getStyle()		{ return $this->style; }
	public function getType()		{ return $this->type; }
    
}

