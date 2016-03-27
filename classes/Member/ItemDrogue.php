<?php
/** Gestion des drogues.
 *
 * @package Member
 * @subpackage Item
 */
class Member_ItemDrogue extends Member_Item
{
	protected $duree;
	protected $boostPa;
	protected $shockPa;
	protected $boostPv;
	protected $shockPv;
	protected $percStatAgi;
	protected $percStatDex;
	protected $percStatPer;
	protected $percStatFor;
	protected $percStatInt;
	protected $melangeId;
	protected $sousType;
	
	function __construct (&$arr)
	{
		parent::__construct($arr);
		
		$this->sousType			= $arr['db_soustype'];
	}
	
	/** Retourne la quantité de l'item en inventaire
	 * @return int
	 */	
	public function getQte()			{ return $this->qte; }
	
	/** Modifie la quantité de l'item en inventaire
	 * @param int $qte Quantité de l'item
	 */	
	public function setQte($qte)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$this->qte = $qte;
		if($this->qte <= 0)
		{
			$query = 'DELETE FROM ' . DB_PREFIX . 'item_inv'
					. ' WHERE inv_id=:id'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':id',		$this->getInvId(),	PDO::PARAM_INT);
			$prep->executePlus($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;	
		}
		else
		{
			$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
					. ' SET inv_qte=:qte'
					. ' WHERE inv_id=:id'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':qte',	$qte,				PDO::PARAM_INT);
			$prep->bindValue(':id',		$this->getInvId(),	PDO::PARAM_INT);
			$prep->executePlus($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
	}
	
	/** Retourne le type affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getType()
	{ 
		switch($this->sousType)
		{
			case 'drogue_drogue':
				return 'Drogue'; 
			case 'drogue_substance':
				return 'Substances';
			case 'drogue_antirejet':
				return 'Anti-Rejets';
			case 'drogue_autre':
				return 'Substance';
		}
	}
	
	
	/** Retourne la durée de l'effet en remise
	 * @return int
	 */
	public function getDuree()			{ return $this->duree; }
	
	/** Retourne le boost de PA lors de l'effet de la drogue. Nombre de PA supplémentaire(en PA, pas en %)
	 * @return int
	 */
	public function getBoostPa()		{ return $this->boostPa; }
	
	/** Retourne le boost de PV lors de l'effet de la drogue. Nombre de PV supplémentaire(en PV, pas en %)
	 * @return int
	 */
	public function getBoostPv()		{ return $this->boostPv; }
	
	/** Retourne la perte subite de PA lorsque l'effet de la drogue expire. Nombre de PA perdu(en PA, pas en %)
	 * @return int
	 */
	public function getShockPa()		{ return $this->shockPa; }
	
	/** Retourne la perte subite de PV lorsque l'effet de la drogue expire. Nombre de PV perdu(en PV, pas en %)
	 * @return int
	 */
	public function getShockPv()		{ return $this->shockPv; }
	
	/** Retourne le % d'affectation de la STAT désirée. Ex.: 120 = AGI * 1.2 , ou 20% d'augmentation.
	 * @return int
	 */
	public function getPercStat($statCode)
	{
		switch(strtoupper($statCode))
		{
			case 'AGI':
				return $this->percStatAgi;
			case 'DEX':
				return $this->percStatDex;
			case 'PER':
				return $this->percStatPer;
			case 'FOR':
				return $this->percStatFor;
			case 'INT':
				return $this->percStatInt;
			default:
				throw new Exception('Stat inconnue: (' . $statCode . ')');
				return false;
		}
	}
	
}


