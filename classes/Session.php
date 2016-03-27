<?php
/** Gestion de la session (Classe Singleton).
* <br> (cette classe est instanciée pour toutes les pages du site)
*
* @package CyberCity_2034
*/
class Session
{
	private $sessId;
	
	private static $instance;
	
	/**
	 * Charge la session en mémoire.
	 * Si la session est authentifiée, la classe Member_Account -qui est toujours déjà instanciée- sera chargée/rempli, et ce pour toutes les pages du site.
	 * (en ce qui concerne la classe Member_Perso, elle sera instanciée uniquement pour les pages la nécéssitant)
	 * 
	 */ 
	public function __construct()
	{
		session_start();
		
		$this->sessionStart();
	}
	
	public static function load()
	{
		if(empty(self::$instance))
			self::$instance = new Session();
		
		return self::$instance;
	}

	/** 
     * Prévient les utilisateurs sur le clônage de l'instance.
	 */ 
    public function __clone()
    {
        trigger_error('Le clônage n\'est pas autorisé.', E_USER_ERROR);
    }
	
	//#########################
	//## GESTION DE BASE DE LA SESSION
	//#########################
	
	/**
	 * Initialise la session.
	 */
	private function sessionStart()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$db->beginTransaction();

		
		$this->sessId = $this->getCookieNo();
		
		if (empty($this->sessId)) //Si le cookie existe pas, le créer
		{
			$this->sessId = $this->generateSessId();
			setcookie('sessId', $this->sessId, time()+60*60*24*30); //Créer le cookie (expire après 30 jours)
		}
		elseif (!isset($_COOKIE['sessId']))
		{
			fctErrorMSG("Votre navigateur ne supporte pas les cookies, vous devez les accepter pour ce site.");
			die();
		}
		
		
		
		$this->createOrExtendSession();
		
		
		$db->commit();
		
		
		
		//Effacer les sessions expirées
		$query = 'DELETE FROM ' . DB_PREFIX . 'session'
					. ' WHERE expiration < :expiration;';
		$prep = $db->prepare($query);
		$prep->bindValue(':expiration', CURRENT_TIME,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
	
	



	
	
	/**
	 * Génère un # de cookie en s'assurant qu'il soit unique.
	 *
	 * @return string # du cookie session, NULL si aucun
	 */ 
	private function generateSessId()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//S'assurer que le # de session ne soit pas déjà utiliser par un autre joueur.
		//(Les chances sont faible, mais dans un tel cas, ca risquerais de poser des problèmes importants.)
		$query = 'SELECT idcookie'
					. ' FROM `' . DB_PREFIX . 'account`'
					. ' WHERE `idcookie`=:idcookie'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		do
		{
			$computer_idcookie = fctRandomString(50);
			
			//Rechercher le # de cookie
			$prep->bindValue(':idcookie', $computer_idcookie,	PDO::PARAM_STR);
			$prep->executePlus($db, __FILE__, __LINE__);
			$ret = $prep->fetch();
			
			
		}while($ret!==false);
		$prep->closeCursor();
		$prep = NULL;
			
		return $computer_idcookie;
		
	}
	
	
	/** 
	 * Créé la session ou prolonge son expiration
	 */ 
	private function createOrExtendSession()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$expireTime = mktime(date('H'), date('i')+SESSION_TIMEOUT, date('s'), date('m'), date('d'), date('Y'));
		
		
		//Détecter si une session est déjà lancée
		$query='SELECT userId'
					. ' FROM ' . DB_PREFIX. 'session'
					. ' WHERE idcookie=:idcookie'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':idcookie', $this->sessId,	PDO::PARAM_STR);
		$prep->executePlus($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		
		if ($arr === false) //Aucune session existante, la créer
		{
			$query='INSERT INTO `' . DB_PREFIX . 'session`'
						. ' ( `ip`, `userId`, `idcookie` , `expiration` )'
						. ' VALUES ('
							. ' :ip, :userId, :idCookie, :expiration'
						. ' );';
			$prep = $db->prepare($query);
			$prep->bindValue(':ip', $_SERVER['REMOTE_ADDR'],	PDO::PARAM_STR);
			$prep->bindValue(':userId', $this->getVar('userId'),	PDO::PARAM_INT);
			$prep->bindValue(':idCookie', $this->sessId,	PDO::PARAM_STR);
			$prep->bindValue(':expiration', $expireTime,	PDO::PARAM_INT);
			$prep->executePlus($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
		}
		else //Session existante, la prolonger
		{
			
			$query='UPDATE `' . DB_PREFIX. 'session`'
						. ' SET `expiration` = :expiration'
						. ' WHERE `idcookie` = :idCookie'
						. ' LIMIT 1 ;';
			$prep = $db->prepare($query);
			$prep->bindValue(':idCookie', $this->sessId,	PDO::PARAM_STR);
			$prep->bindValue(':expiration', $expireTime,	PDO::PARAM_INT);
			$prep->executePlus($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
		}
		
	}
	
	
	
	/**
	 * Changer et stocker une variable session.
	 * NOTE: Les variables sont fixe, vous ne pouvez pas en créer de nouvelles dynamiquement sans modifier la classe et la structure MySQL avant.
	 * 
	 * Exemple d'utilisation:
	 * <code>
	 * $session->setVar('persoList', $perso_list);
	 * </code>
	 *
	 * @param string $varName Nom de la variable
	 * @param string $varValue Valeur de la variable (peut être un tableau)
	 */ 
	public function setVar($varName, $varValue)
	{
		if($varName === 'userId')
		{
			$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
			$db = $dbMgr->getConn('game'); //Demander la connexion existante
			
			$query='UPDATE `' . DB_PREFIX. 'session`'
					. ' SET `userId` = :userId'
					. ' WHERE `idcookie` = :idCookie'
					. ' LIMIT 1 ;';
			$prep = $db->prepare($query);
			$prep->bindValue(':userId', $varValue,	PDO::PARAM_INT);
			$prep->bindValue(':idCookie', $this->sessId,	PDO::PARAM_STR);
			$prep->executePlus($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
		}
		$_SESSION[$varName] = serialize($varValue);
	}
	
	
	
	/**
	 * Retourne le # de session (SessId).
	 *
	 * @return string
	 */ 
	public function getSSID()
	{
		return $this->sessId;
	}
	
	
	
	/**
	 * Retourne le # du cookie
	 *
	 * @return string # du cookie session, NULL si aucun
	 */ 
	private function getCookieNo()
	{
		return isset($_COOKIE['sessId']) ? $_COOKIE['sessId'] : null;	
	}
	
	/**
	 * Retourne une variable session
	 *
	 * @param string $varName Nom de la variable
	 * @return selon_le_type
	 */ 
	public function getVar($varName)
	{
		return isset($_SESSION[$varName]) ? unserialize($_SESSION[$varName]) : NULL;
	}

	
}


