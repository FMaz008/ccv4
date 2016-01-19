<?php
/** Gestion de la nourriture.
*
* @package Member
* @subpackage Item
*/
class Member_ItemNourriture extends Member_Item
{
	private $pn;
	
	function __construct (&$arr)
	{
		parent::__construct($arr);
		
		$this->pn		= $arr['inv_pn'];
	}
	
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
			$prep->execute($db, __FILE__,__LINE__);
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
			$prep->execute($db, __FILE__,__LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
	}

	/**
	 * Modifier le nombre de PN de l'item.
	 * 
	 * Cette méthode s'occupe de calculer elle même le total et
	 * s'assure que pnMax n'est pas dépassé.
	 * IMPORTANT: pour sauvegarder en DB les modifications,
	 * vous DEVEZ apeller {@link setPn()}.
	 *
	 * @see setPn()
	 * @param char $plusMoins Prend la valeur '+' ou '-' selon si on désire ajouter ou retirer des PN
	 * @param float $nbrPa Nombre de PN à ajouter ou retirer
	 */
	public function changePn($plusMoins, $nbrPn)
	{
		if(($plusMoins!='+' && $plusMoins!='-') || !is_numeric($nbrPn))
		{
			throw new Exception('Paramètres de type invalide.');
			return false;
		}
		
		if($plusMoins=='+')
		{
			if($this->pn < $this->pnMax)
				if( ($this->pn + $nbrPn) <= $this->pnMax)
					$this->pn+=$nbrPn;
				else
					$this->pn = $this->pnMax;
		}
		elseif($plusMoins=='-')
			$this->pn-=$nbrPn;
			
	}

	/**
	 * Modifier directement le nombre de PN de l'item.
	 *
	 * Cette méthode modifie directement le total.
	 * NOTE: La méthode {@link changePn()} permet d'effectuer
	 * automatiquement les calculs du total.
	 *
	 * @see changePn()
	 * @param float $nbrPn Nombre de PN de l'item.
	 */
	public function setPn($nbrPn = false)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		if($nbrPn === false)
			$nbrPn = $this->getPn();
		
		$query = 'UPDATE ' . DB_PREFIX . 'item_inv'
				. ' SET inv_pn=:pn'
				. ' WHERE inv_id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':pn',	$nbrPn,				PDO::PARAM_INT);
		$prep->bindValue(':id',	$this->getInvId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		$this->pn = $nbrPn;
	}
	
	/** Retourne la quantité de PN que l'item contiend
	 * @return int
	 */	
	public function getPn()				{ return $this->pn; }
	
	
	/** Retourne le groupe affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroup()			{ return 'Consommable'; }
	
	/** Retourne le groupe (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getGroupTech()		{ return 'consommable'; }
	
	/** Retourne le type affichable de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getType()			{ return 'Nourriture'; }
	
	/** Retourne le type (nom technique) de l'item (pour l'affichage)
	 * @return string
	 */	
	public function getTypeTech()		{ return 'nourriture'; }

}


