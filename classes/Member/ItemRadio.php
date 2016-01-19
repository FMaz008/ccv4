<?Php
/** Gestion des Talkie Walkies
 *
 * @package Member
 * @subpackage Item
 */

class Member_ItemRadio extends Member_Item
{
	private $frequence;
	private $clef;
	private $silenceCapable;
	private $cryptCapable;
	
	function __construct(&$arr)
	{
		parent::__construct($arr);
		
		$this-> frequence = $arr['inv_notel'];
		$this-> clef = $arr['inv_nip'];
		$this-> silenceCapable = $arr['db_afficheur'];
		$this-> cryptCapable = $arr['db_anonyme'];
	}
	
	
	/** Retourne la fréquence sur laquelle est réglée la radio
	 * @return string
	 */
	public function getFrequence()      { return $this->frequence ; }
	
	
	/** Retourne la clef de cryptage de la radio
	 * @return string
	 */
	public function getClef()           { return $this->clef ;} 	
	
	/** Retourne True si la radio possède un dispositi silencieux
	 * @return bool
	 */
	public function getSilenceCapable() { return ($this->silenceCapable==1) ? false : true ; } 	
	
	/** Retourne True si la radio peut crypter les messages
	 * @return bool
	 */
	public function getCryptCapable()   { return ($this->cryptCapable==1) ? true : false ;} 
	
	/** Retourne True si une fréquence est réglé ( et que donc on peut parler)
	 * @return bool
	 */
	public function getCanTalk()		{ return(($this->frequence == 0) || ($this->frequence == "")) ? false : true ;}
	
	/** Retourne le groupe affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroup()			{ return 'Électronique'; }
	
	/** Retourne le groupe (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroupTech()		{ return 'electronique'; }
	
	/** Retourne le type affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getType()			{ return 'Radio (Talkie Walkie)'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getTypeTech()		{ return 'talkiewalkie'; }
	
	/** Met a jour la Clef de cryptage
	 * @param int $clef Nouvelle clé de cryptage
	 */
	public function majClef($clef)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
				. ' SET inv_nip=:nip'
				. ' WHERE inv_id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':nip',	$clef,				PDO::PARAM_INT);
		$prep->bindValue(':id',		$this->getInvId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
	
	/** Met à jour la fréquence de la radio
	 * @param int $frequence Nouvelle fréquence de la radio
	 */
	public function majFrequence($frequence)
	{
		if(!is_numeric($frequence))
			return false;
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
	
		$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
				. ' SET inv_notel=:freq'
				. ' WHERE inv_id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':freq',	$frequence,			PDO::PARAM_INT);
		$prep->bindValue(':id',		$this->getInvId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;

		$this->frequence = $frequence;
	}
	
	/** Envoie un message radio
	 * @param Member_Perso $perso Instance du perso qui envoi le message
	 * @param string $message Message à envoyer
	 */
	public function utiliser(&$perso,$message)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		$errorUrl = '?popup=1&amp;m=Action_Radios';
		
			
		if(!$this->getCanTalk())
			return fctErrorMSG('Aucune fréquence n\'a été réglée vous ne pouvez pas parler.', $errorUrl);
		
		
		$enteteDestinataire = "Vous entendez quelqu'un parler depuis la radio branchée sur la fréquence: " . $this->getFrequence();
		
		
		
		//Recup des talkies sur la même frequence que celui de l'utilisateur
		$query = 'SELECT DISTINCT *'
				. ' FROM '.DB_PREFIX.'item_inv'
				. ' LEFT JOIN ' . DB_PREFIX . 'item_db ON (db_id=inv_dbid) '
				. ' LEFT JOIN ' . DB_PREFIX . 'perso ON (id=inv_persoid)'
				. ' WHERE inv_notel=:freq'
					. ' AND inv_id!=:id'
					. ' AND inv_persoid IS NOT NULL'
					. ' AND db_type="talkiewalkie";'; 
		$prep = $db->prepare($query);
		$prep->bindValue(':freq',	$this->getFrequence(),	PDO::PARAM_INT);
		$prep->bindValue(':id',		$this->getInvId(),		PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arrRadio = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		
		
		//Parcourir toutes les radios qui captent la fréquence de celle de l'envoyeur.
		$arrDestOreilletteClair = array(); //Tableau des destinataire (ceux possédant une radio avec oreillette)
		$arrDestOreilletteCrypt = array(); //Tableau des destinataire (ceux possédant une radio avec oreillette)
		$arrToClair = array();	//Tableau concernant les perso dans les lieux
		$arrToCrypt = array();	//Tableau concernant les perso dans les lieux
		
		foreach($arrRadio as $arrRadioTo)
		{
			//Construire la liste de ceux qui recoivent la transmission (et qui ont un radio)
			//From, du point de vu de ceux qui vont recevoir le message
			//(To sera les gens qui entendent la radio de From qui la possède, $perso est l'émetteur réel)
			$persoFrom = new Member_Perso($arrRadioTo);
			$radio = new Member_ItemRadio($arrRadioTo);
			
			$lieuTech = $persoFrom->getLieu()->getNomTech(); //Lieu Technique du perso ayant la radio réceptrice
			
			
			//S'il y a une oreillette, ajouter le perso dans une liste spéciale où il ne notifira pas les autres
			if($radio->getSilenceCapable())
			{
				
				
				//Si le message peut-être décrypté
				if($radio->getClef() === $this->getClef())
					$this->addWithoutDouble($arrDestOreilletteClair, $persoFrom->getId());
					//$arrDestOreilletteClair[] = $persoFrom->getId();
				else
					$this->addWithoutDouble($arrDestOreilletteCrypt, $persoFrom->getId());
					//$arrDestOreilletteCrypt[] = $persoFrom->getId();
				
				
				
			//S'il n'y a pas d'oreillette, ajouter tout les perso présent dans le lieu
			}
			else
			{
				
				
				//Si le message peut-être décrypté
				if($radio->getClef() === $this->getClef())
				{
				
					//Ajouter le perso qui recoit le message
					$this->addFromTo($arrToClair, $lieuTech, 'from', $persoFrom->getId());
					
					$i=0;
					while( $persoTo = $persoFrom->getLieu()->getPerso($persoFrom, $i++))
						if($persoTo->getId() != $persoFrom->getId())
							$this->addFromTo($arrToClair, $lieuTech, 'to', $persoTo->getId());
				
				}
				else
				{
					
					//Ajouter le perso qui recoit le message
					$this->addFromTo($arrToCrypt, $lieuTech, 'from', $persoFrom->getId());
					
					$i=0;
					while( $persoTo = $persoFrom->getLieu()->getPerso($persoFrom, $i++))
						if($persoTo->getId() != $persoFrom->getId())
							$this->addFromTo($arrToCrypt, $lieuTech, 'to', $persoTo->getId());
					
				}
			}
		}
		
		
		//### AJOUTER LES MESSAGES DANS LES HE
		
		//Message du personnage qui l'envoi
		$to = array();
		if(!$this->getSilenceCapable())
		{
			$i=0; $e=0;
			while( $persoTo = $perso->getLieu()->getPerso($perso, $i++))
				$to[$e++] = $persoTo->getId();
		}
		Member_He::add($perso->getId(), $to , 'radio', "Une transmission radio est envoyée:\n " . $message, HE_TOUS, HE_TOUS); //HE_AUCUN)
		
		
		//Seulement pour les perso avec une oreillette
		if(count($arrDestOreilletteClair)>0)
			Member_He::add('System', $arrDestOreilletteClair, 'radio', "Une transmission radio se fait entendre:\n " . $message, HE_AUCUN, HE_UNIQUEMENT_MOI);
		
		if(count($arrDestOreilletteCrypt)>0)
			Member_He::add('System', $arrDestOreilletteCrypt, 'radio', "Une transmission radio se fait entendre:\n (charabia incompréhensible)", HE_AUCUN, HE_UNIQUEMENT_MOI);
		
		
		//Pour chaque lieu
		if(count($arrToClair)>0)
			foreach($arrToClair as $arr)
				Member_He::add($arr['from'], $arr['to'], 'radio', "Une transmission radio se fait entendre:\n" . $message,HE_TOUS, HE_TOUS);
		
		if(count($arrToCrypt)>0)
			foreach($arrToCrypt as $arr)
				Member_He::add($arr['from'], $arr['to'], 'radio', "Une transmission radio se fait entendre:\n (charabia incompréhensible)",HE_TOUS, HE_TOUS);
		
		
	}
	
	/** Fonction interne utiliser par la méthode utiliser afin d'effectuer une validation de doublon lors du remplissage du tableau des envois.
	 * @param array $arr tableau à remplir
	 * @param string $lieuTech Nom technique auquel on ajoute des destinataire du message
	 * @param string $fromTo Prend la valeur 'from' ou 'to' selon le type d'envoi désiré.
	 * @param int $id Id du perso à ajouter
	 * @return bool Retournera False si un doublon existe et qu'aucun ajout est fait, sinon true.
	 */
	private function addFromTo(&$arr, $lieuTech, $fromTo, $id)
	{
		
		//Vérifier si l'entré du lieu est innexistante, si c'est le cas, la créer.
		if(!isset($arr[$lieuTech]))
			$arr[$lieuTech] = array('from'=>array(), 'to'=>array());
		
		//Vérifier si le perso est déjà présent
		foreach($arr[$lieuTech][$fromTo] as $pid)
			if($pid == $id)
				return false;
		
		//Si innexistant, l'ajouter au tableau
		$arr[$lieuTech][$fromTo][count($arr[$lieuTech][$fromTo])] = $id;
		return true;
	}

	/** Fonction interne utilisée pour ajouter un id dans un tableau sans faire de doublon
	 * @param array $arr tableau à remplir
	 * @param int $id Id du perso à ajouter
	 */
	private function addWithoutDouble(&$arr, $id)
	{
		$exist = false;

		//on vérifie si l'id existe déjà
		foreach($arr as $arrId)
			if($arrId == $id)
				$exist = true;

		//s'il n'existe pas on l'ajoute
		if(!$exist)
			$arr[] = $id;
	}
	
}

