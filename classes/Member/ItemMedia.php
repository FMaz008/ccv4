<?php
/** Gestion des items médias
 * 
 * @package Member
 * @subpackage Item
 */
 
class Member_ItemMedia extends Member_Item
{
	private $sens;
	private $mediaType;
	
	function __construct($arr)
	{
		parent::__construct($arr);
		$this->sens = 		$arr['db_internet'];
		$this->mediaType = 	$arr['db_pass'];
	}
	
	/** Retourne si l'item est un emetteur
	 * @return bool
	 */
	public function isEmetteur()		{ return $this->sens == 1; }
	
	/** Retourne si l'item est un recepteur
	 * @return bool
	 */
	public function isRecepteur()		{ return $this->sens == 0; }
	
	/** Retourne le type de média accessible (tele, radio ou tous)
	 * @return bool
	 */
	public function getMediaType()		{ return $this->mediaType; }
	
	/** Retourne le type de média accessible à affichable(télé ou radio ou tous)
	 * @return string
	 */
	public function getMediaTypeAffichable(){ 
		if($this->mediaType == "tele")
			return "Télé";
		if($this->mediaType == "radio")
			return "Radio";
		return "Tous";
	}
	
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
	public function getType()			{ return 'Média'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getTypeTech()		{ return 'media'; }
	
	/** Retourne le contenu du média accessible
	 * 
	 * @return array
	 */
	public function getMediaContenu($type, $channel)
	{
		if($this->mediaType != 'tous')
			if($this->mediaType != $type)
			{
				throw new GameException('Type de média non supporté par l\'item ' . $this->invId . '.');
				return;
			}
			
		return Member_MediaFactory::getAllMessage($type, $channel);
	}
	
	public function writeInMedia($type, $channel, $title, $message)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if($this->mediaType != 'tous')
			if($this->mediaType != $type)
			{
				throw new GameException('Type de média non supporté par l\'item ' . $this->invId . '.');
				return;
			}
		
		$timestamp = time();
		
		$query = 'INSERT INTO `' . DB_PREFIX . 'media`'
				. ' (`mediaType`, `canalId`, `date`, `titre`, `message`)'
				. ' VALUES '
				. ' (:mediaType, :canalId, :date, :titre, :msg);';	
		$prep = $db->prepare($query);
		$prep->bindValue(':mediaType',	$type, PDO::PARAM_STR);
		$prep->bindValue(':canalId',	$channel, PDO::PARAM_INT);
		$prep->bindValue(':titre',	$title, PDO::PARAM_STR);
		$prep->bindValue(':msg',	$message, PDO::PARAM_STR);
		$prep->bindValue(':date',	$timestamp, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
}