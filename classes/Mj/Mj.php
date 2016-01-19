<?php
/**
 * Gestion d'un Mj.
 * <br> (cette classe est instanciée uniquement si l'account à les privilège MJ)
 * 
 * @package Mj
 */
class Mj_Mj
{
	
	private $id;
	private $userId;
	private $nom;
	private $poste;
	private $email;
	private $present;
	private $axPpa;
	private $axEj;
	private $axHj;
	private $axAdmin;
	private $lastConnection;
	private $axDev;
	
	
	/**
	 * Charge une base de compte en mémoire (options générales).
	 * <br> Si la session est authentifiée, la classe Member_Account -qui est toujours déjà instanciée- sera chargée/rempli, et ce pour toutes les pages du site.
	 * <br> (en ce qui concerne la classe Member_Perso, elle sera instanciée uniquement pour les pages la nécéssitant)
	 *
	 */
	function __construct(&$arr)
	{
		$this->id		= $arr['id'];
		$this->userId	= $arr['userId'];
		$this->nom		= $arr['nom'];
		$this->poste	= $arr['poste'];
		$this->email	= $arr['email_prefix'];
		$this->present	= $arr['present'];
		$this->axPpa	= $arr['ax_ppa'];
		$this->axEj		= $arr['ax_ej'];
		$this->axHj		= $arr['ax_hj'];
		$this->axAdmin	= $arr['ax_admin'];
		$this->lastConnection	= $arr['last_connection'];
		$this->axDev	= $arr['ax_dev'];
	}
	

	

	/**
	 * Retourne l'ID du MJ
	 * @return int
	 */	
	public function getId(){						return $this->id; }
	

	/**
	 * Retourne l'ID du compte (account) associé à cet accès MJ
	 * @return int
	 */	
	public function getUserId(){					return $this->userId; }
	
	
	/**
	 * Retourne le nom affichable du MJ
	 * @return string
	 */	
	public function getNom(){						return $this->nom; }
	

	/**
	 * Retourne le poste affichable du MJ
	 * @return string
	 */	
	public function getPoste(){						return $this->poste; }
	

	/**
	 * Retourne le email du MJ
	 * @return string
	 */	
	public function getEmail(){						return $this->email . '@' . SITE_DOMAIN; }
	
	
	/**
	 * Retourne si Oui ou Non le MJ est présent (false = en vacance)
	 * @return bool
	 */	
	public function isPresent(){					return ($this->present==1) ? true:false; }

	/**
	 * Défini si Oui ou Non le MJ est présent (false = en vacance)
	 */	
	public function setPresent($statut)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$this->present = (($statut)?1:0);
		$query = 'UPDATE ' . DB_PREFIX . 'mj'
				. ' SET present= :present'
				. ' WHERE id= :id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':present',	$this->present,	PDO::PARAM_INT);
		$prep->bindValue(':id',			$this->id,		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
	}
	
	/**
	 * Retourne si Oui ou Non le MJ à accès aux PPA
	 * @return bool
	 */	
	public function accessPpa(){					return ($this->axPpa==1) ? true:false; }
	
	
	/**
	 * Retourne si Oui ou Non le MJ à accès aux contrôle IJ
	 * @return int
	 */	
	public function accessEj(){						return ($this->axEj==1) ? true:false; }
	
	
	/**
	 * Retourne si Oui ou Non le MJ à accès aux contrôle HJ
	 * @return bool
	 */	
	public function accessHj(){						return ($this->axHj==1) ? true:false; }

	/**
	 * Retourne si Oui ou Non le MJ à accès aux contrôle développement
	 * @return bool
	 */	
	public function accessDev(){					return ($this->axDev==1) ? true:false; }
	
	/**
	 * Retourne si Oui ou Non le MJ à accès aux contrôle HJ
	 * @return bool
	 */	
	public function accessAdmin(){					return ($this->axAdmin==1) ? true:false; }
	
	/**
	 * Retourne la date de la dernière connexion au format affichable
	 * @return date
	 */	
	public function getLastConnection(){			return $this->lastConnection; }
	
	/**
	 * Ajoute un message au HE du MJ
	 *
	 * @param string $msg Message à ajouter au HE.
	 * @param string $concernant Qui ou quoi est concerné par cet évènement (Ex.: nom d'un personnage)
	 * @param string $type Type d'entité étant concerné par l'évènement (Ex.: perso, compte, system, mj, lieu, item)
	 *
	 * @return bool True si tout est ok
	 */
	public function addHe($msg, $concernant, $type)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'INSERT INTO ' . DB_PREFIX . 'mj_he'
					. ' (`msg`,`mjId`,`date`,`concernant`, `concernant_type`)'
					. ' VALUES'
					. ' (:msg, :mjId, UNIX_TIMESTAMP(), :concernant, :concernant_type);';
		$prep = $db->prepare($query);
		$prep->bindValue(':msg',			$msg,			PDO::PARAM_STR);
		$prep->bindValue(':mjId',			$this->getId(),	PDO::PARAM_INT);
		$prep->bindValue(':concernant',		$concernant,	PDO::PARAM_STR);
		$prep->bindValue(':concernant_type',$type,			PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
		
		
		return true;
	}
}


