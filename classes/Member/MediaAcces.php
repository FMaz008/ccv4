<?php
/** Gestion des accès média
 *
 * Exemple d'utilisation : 
 * <code>
 * $query = 'SELECT * FROM ' . DB_PREFIX . 'lieu_medias WHERE id = :id;';
 * $prep = $db->prepare($query);
 * $prep->bindValue(':id',	345,	PDO::PARAM_INT);
 * $prep->execute($db, __FILE__, __LINE__);
 * $arr = $prep->fetch();
 * $prep->closeCursor();
 * $prep = NULL;
 *
 * $mediaAcces = new Member_MediaAcces($arr);
 * echo "Vous pouvez accéder au cannal " . $mediaAcces->getChannelId();
 * </code>
 *
 * @package Member
 * @subpackage MediaAcces
 */
class Member_MediaAcces
{
	/** Id de l'accès média
	 * @var int
	 * @access private
	 */
	private $id;
	/** Id du lieu où se situe l'accès média
	 * @var int
	 * @access private
	 */
	private $lieuId;
	/** Nom affichable de l'accès média
	 * @var string
	 * @access private
	 */
	private $nom;
	/** Type de média accessible (tele, radio, tous)
	 * @var string
	 * @access private
	 */
	private $mediaType;
	/** Canal sur lequel est branché l'accès
	 * @var int
	 * @access private
	 */
	private $channelId;
	/** Type d'interaction avec les médias (0 = reception, 1 = emission)
	 * @var int
	 * @access private
	 */
	private $interactionType;
	
	function __construct($arr)
	{
		$this->id				= $arr['id'];
		$this->lieuId			= $arr['lieuid'];
		$this->nom				= $arr['nom'];
		$this->mediaType		= $arr['mediaType'];
		$this->channelId		= $arr['canalId'];
		$this->interactionType	= $arr['interactionType'];
	}
	
	public function getId()					{	return $this->id; }
	
	public function getLieuId()				{	return $this->lieuId; }
	
	public function getNom()				{ 	return $this->nom; }
	
	public function getMediaType()			{ 	return $this->mediaType; }
	
	public function getChannelId()			{ 	return $this->channelId; }
	
	public function isEmetteur()			{ 	return ($this->interactionType == 1); }
	
	public function isRecepteur()			{	return ($this->interactionType == 0); }
	
	public function getMediaTypeAffichable()
	{
		if($this->mediaType == 'tele')
			return 'Télé';
		else
			return 'Radio';
	}
	
	public function getMediaContenu()
	{
		return Member_MediaFactory::getAllMessage($this->mediaType, $this->channelId);
	}
	
	public function writeInMedia($title, $message)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$timestamp = time();
		
		$query = 'INSERT INTO `' . DB_PREFIX . 'media`'
				. ' (`mediaType`, `canalId`, `date`, `titre`, `message`)'
				. ' VALUES '
				. ' (:mediaType, :canalId, :date, :titre, :msg);';	
		$prep = $db->prepare($query);
		$prep->bindValue(':mediaType',	$this->mediaType, PDO::PARAM_STR);
		$prep->bindValue(':canalId',	$this->channelId, PDO::PARAM_INT);
		$prep->bindValue(':titre',	$title, PDO::PARAM_STR);
		$prep->bindValue(':msg',	$message, PDO::PARAM_STR);
		$prep->bindValue(':date',	$timestamp, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
	
	public function changeChannelId($channelId)
	{
		$this->channelId = $channelId;
	}
	
	public function changeNom($name)
	{
		$this->nom = $name;
	}
	
	public function changeMediaType($mediaType)
	{
		$this->mediaType = $mediaType;
	}
	
	public function changeInteractionType($interactionType)
	{
		$this->interactionType = $interactionType;
	}
	
	public function changeLieuId($lieuId)
	{
		$this->lieuId = $lieuId;
	}
	
	public function setChannelId()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'UPDATE `' . DB_PREFIX . 'lieu_medias`'
				. ' SET `canalId` = :channelId'
				. ' WHERE `id` = :id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':channelId',	$this->channelId, PDO::PARAM_INT);
		$prep->bindValue(':id',	$this->id, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
	
	public function setNom()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'UPDATE `' . DB_PREFIX . 'lieu_medias`'
				. ' SET `nom` = :nom'
				. ' WHERE `id` = :id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':nom',	$this->nom, PDO::PARAM_STR);
		$prep->bindValue(':id',	$this->id, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
	
	public function setMediaType()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'UPDATE `' . DB_PREFIX . 'lieu_medias`'
				. ' SET `mediaType` = :mediaType'
				. ' WHERE `id` = :id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':mediaType',	$this->mediaType, PDO::PARAM_STR);
		$prep->bindValue(':id',	$this->id, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
	
	public function setinteractionType()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'UPDATE `' . DB_PREFIX . 'lieu_medias`'
				. ' SET `interactionType` = :interactionType'
				. ' WHERE `id` = :id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':interactionType',	$this->interactionType, PDO::PARAM_INT);
		$prep->bindValue(':id',	$this->id, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
	
	public function setLieuId()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'UPDATE `' . DB_PREFIX . 'lieu_medias`'
				. ' SET `lieuid` = :lieuId'
				. ' WHERE `id` = :id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieuId',	$this->lieuId, PDO::PARAM_INT);
		$prep->bindValue(':id',	$this->id, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
}