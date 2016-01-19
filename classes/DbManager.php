<?php

/** 
 * Gestion des connexions aux bases de données.
 * 
 * Cette classe s'occupe de la gestion des accès aux base de données
 * Il s'agit d'une classe singleton qui doit être utilisée comme ceci:
 *
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.0
 * @package CyberCity_2034
 */
class DbManager
{
	// PARTIE 1 - Implémentation du singleton
	/**
	 * Tableau des connexions.
	 * @var Object
	 * @access private
	 */
    private static $instance;


	/**
	 * Tableau des connexions
	 * @var array
	 * @access private
	 */
    private $arrConn;
    
    /** 
     * Constructeur privé du singleton qui défini la variable par défaut.
     *
	 * USAGE SYSTÈME SEULEMENT
	 */ 
    private function __construct()
    {
    	$this->arrConn = array();
    }

    /**
     * Instancie ou retourne (singleton) l'instance du gestionnaire de connexions.
	 *
	 * Exemple d'utilisation - Charger le gestionnaire de connexion
	 * <code>
	 * //Instancier le gestionnaire
	 * $dbMgr = DbManager::getInstance();
	 * </code>
	 *
	 * @return DbManager Retourne une instance de DbManager
	 */ 
    public static function getInstance() 
    {
        if (empty(self::$instance))
            self::$instance = new DbManager();
        
		//Retourner l'intance de la classe DbManager
        return self::$instance;
    }

    /** 
     * Prévient les utilisateurs sur le clônage de l'instance.
	 */ 
    public function __clone()
    {
        trigger_error('Le clônage n\'est pas autorisé.', E_USER_ERROR);
    }
    
    
    
    //PARTIE 2 - Le gestionnaire de connexion
    
	
    
    /** 
     * Valide si un nom de connexion existe.
	 *
	 * @param string $db_host Nom de la connexion 
 	 * @return bool
	 */ 
    public function connExist($connName)
    {
		return array_key_exists($connName, $this->arrConn);
    }
    
    
    /** 
     * Créer et stocke une nouvelle connexion à la base de données.
     * 
	 * Exemple d'utilisation - Établir une nouvelle connexion.
	 * <code>
	 * //Instancier le gestionnaire
	 * $dbMgr = DbManager::getInstance();
	 * try
	 * {
	 *  	//Créer la connexion
	 *  	$gameCon = $dbMgr->newConn('game', DB_HOST, DB_USER, DB_PASS, DB_BASE);
	 * }
	 * catch (Exception $e)
	 * {
	 * 	die('Impossible d\'établir la connexion: ' . $e->getMessage());
	 * }
	 * </code>
	 *
	 * @param string $db_host Nom de la connexion
	 * @param string $db_host Hote de la base de données
	 * @param string $db_user Utilisateur
	 * @param string $db_pass Mot de passe
	 * @param string $db_base Nom de la base de données à utiliser
	 * @param string $db_type Type de base de données (par défaut: 'mysql')
	 * @return MySQLConnection|mixed Selon le type de connexion
	 */ 
    public function newConn($connName, $db_host, $db_user, $db_pass, $db_base, $db_type='mysql')
    {
    	//Rechercher si une connexion existe déjà
       	if($this->connExist($connName))
    	{
    		throw new Exception('Une connexion de ce nom existe déjà. (' . $connName . ')');
    		return false;
    	}
    	
    	//Créer une nouvelle connexion
    	try
    	{
    		switch($db_type)
    		{
    			case 'mysql':
    				//$this->arrConn[$connName] = new MySQLConnection($db_host, $db_user, $db_pass, $db_base);
    				//$this->arrConn[$connName]->query("SET NAMES 'utf8'");
    				$this->arrConn[$connName] = new PDOPlus('mysql:host=' . $db_host . ';dbname=' . $db_base, $db_user, $db_pass,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
					$this->arrConn[$connName]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    				break;
    			default:
    				throw new Exception('Type de base de données non pris en charge. (' . $db_type . ')');
    				return false;
    				break;
    		}
    	}
    	catch (Exception $e)
    	{
    		throw $e; //Transférer l'exception de la connexion
    		return false;
    	}
		
    	
    	
    	//Retourner l'instance de la connexion
    	return $this->arrConn[$connName];
    }
    
    
	/**
	 * Retourne une connexion existante.
	 *
	 * Exemple d'utilisation - Récupérer une connexion
	 * <code>
	 * //Instancier le gestionnaire
	 * $dbMgr = DbManager::getInstance();
	 * try
	 * {
	 *  	//Demander la connexion existante
	 *  	$gameCon = $dbMgr->getConn('game');
	 * }
	 * catch (Exception $e)
	 * {
	 * 	die('Impossible de récupérer la connexion: ' . $e->getMessage());
	 * }
	 * </code>
	 * 
	 * @param string $connName Nom de la connexion
	 * @return MySQLConnection|mixed Selon le type de connexion
	 */ 
    public function getConn($connName)
    {
    	if($this->connExist($connName))
    		return $this->arrConn[$connName];
    	
    	throw new Exception('Connexion innexistante. (' . $connName . ')');
    }
    
    
	/** 
	 * Ferme une connexion existante.
	 *
	 * Exemple d'utilisation - Ferme une connexion
	 * <code>
	 * //Fermer la connexion
	 * $dbMgr->closeConn('game');
	 * </code>
	 *
	 * @param string $connName Nom de la connexion
	 * @return bool
	 */ 
    public function closeConn($connName)
    {
    	if(!$this->connExist($connName))
    	{
    		throw new Exception('Cette connexion n\'existe pas. (' . $connName . ')');
    		return false;
    	}
   		
   		//Supprimer la connexion
     	//$this->arrConn[$connName]->__destruct();
     	unset($this->arrConn[$connName]);
     	return true;
    }
    
    
	/** 
	 * Clos automatiquement toutes les connexions.
	 */ 
    public function __destruct()
    {
    	//Supprimer toutes les connexions
		//unset($this->arrConn);
    }
}


