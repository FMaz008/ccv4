<?php
/** Gestion des ordinateurs.
 *
 * @package Member
 * @subpackage Item
 */
class Member_ItemCartememoire extends Member_Item
{

	private $memory;
	private $memorySize;
	private $key;
	private $mcWrite;
	private $invParam;
	private $permMemory;
	
	function __construct(&$arr)
	{
		parent::__construct($arr);
		
		$this->memory			= stripslashes($arr['inv_memoiretext']);
		$this->memorySize		= $arr['db_capacite'];
		$this->key				= $arr['inv_nip'];
		$this->mcWrite			= $arr['db_mcwrite'];
		$this->invParam			= $arr['inv_param'];
		$this->permMemory		= stripslashes($arr['db_param']);
	}
	
	
	/** Met a jour le contenu d'une memoire
	 * @param string $content Contenu à sauvegarder dans la carte mémoire
	 */
	public function setMemory($content)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		if($content != $this->getMemory())
		{
			$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					. ' SET	inv_memoiretext=:mem'
					. ' WHERE inv_id=:id'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':mem',	$content,			PDO::PARAM_STR);
			$prep->bindValue(':id',		$this->getInvId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			
			$this->memory = $content;
		}
	}
	
	/** Met a jour la clé de cryptage d'une memoire
	 * @param int $key Clé numérique de cryptage, ou '' si aucun cryptage
	 */
	public function setKey($key)
	{
		if($key === '')//transforme un '' en NULL
			$key = NULL;
		
		if($key!=$this->getKey())
		{
			$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
			$db = $dbMgr->getConn('game'); //Demander la connexion existante
			
			
			$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					.  ' SET	inv_nip=:nip'
					. ' WHERE inv_id=:id'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			if($key===NULL)
				$prep->bindValue(':nip',	NULL,		PDO::PARAM_NULL);
			else
				$prep->bindValue(':nip',	$key,		PDO::PARAM_INT);
				
			$prep->bindValue(':id',		$this->getInvId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			$this->key = $key;
		}
	}
	
	
	
	/** Retourne le contenu brute de la mémoire
	 * @return string
	 */	
	public function getMemory()				{ return $this->memory; }
	
	/** Retourne la taille utilisée de la mémoire (en octet/chr)
	 * @return int
	 */	
	public function getMemorySize()			{ return strlen($this->memory); }
	
	/** Retourne la taille maximale de la mémoire (en octet/chr)
	 * @return int
	 */	
	public function getMemorySizeMax()		{ return $this->memorySize; }
	
	/** Retourne TRUE si la carte utilise actuellement une encryption
	 * @return bool
	 */	
	public function isCrypt()				{ return (!empty($this->key)) ? true : false; }
	
	/** Retourne la clé d'encryption de la carte
	 * @return string
	 */	
	public function getKey()				{ return $this->key; }
	
	/** Retourne TRUE si la carte est accessible en écriture
	 * @return bool
	 */	
	public function getMcWrite()			{ return ($this->mcWrite==1) ? true : false; }
	
	
	/** Retourne le contenu par défaut de la mémoire (par exemple une image ou un texte, ceci sera inchangeable)
	 * @return string
	 */	
	public function getPermMemory()			{ return $this->permMemory; }	

	
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
	public function getType()			{ return 'Carte Mémoire'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	

	public function getTypeTech()		{ return 'cartememoire'; }
}


