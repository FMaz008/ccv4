<?php
/** 
 * Gestion de la connexion MySQL.
 *
 * Cette classe s'occupe de la gestion des accès MySQL (Connexion, requête, gestion des erreurs, etc.)
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.1
 * @package CyberCity_2034
 */


class MySQLConnection
{
	/**
	 * Id ressource de la connexion MySQL.
	 * 
	 * @var Ressource
	 * @access private
	 */
	private $connectionId;
	
	/**
	 * Total des requêtes faites.
	 * 
	 * @var int
	 * @access private
	 */
	private $queryCount;
	
	/** 
	 * Somme du temps total requis par les requêtes.
	 * 
	 * @var float
	 * @access private
	 */
	private $sqlTime;
	
	
	/** 
	 * Établi une connexion au serveur MySQL.
	 *
	 * IMPORTANT: Utilisez la classe {@link DbManager} pour établir une connexion.
	 *
	 * @see DbManager
	 * @param string $db_host Hote de la base de données
	 * @param string $db_user Utilisateur
	 * @param string $db_pass Mot de passe
	 * @param string $db_base Nom de la base de données à utiliser
	 */ 
	public function __construct($db_host, $db_user, $db_pass, $db_base)
	{
		if($db_host)
		{
			if(!($this->connectionId=@mysql_connect($db_host,$db_user,$db_pass)))
			{
				throw new Exception('Impossible de se connecter à la base de données. (' . $db_host . ',' . $db_user . ')');
				return false;
			}
				
			if(!mysql_select_db($db_base,$this->connectionId))
			{
				throw new Exception('Impossible de choisir la base de données. (' . $db_base . ')');
				return false;
			}
				
			$this->queryCount=0;
			$this->sqlTime=0;
		}
	}
	
	/** 
	 * Clos automatiquement la connexion MySQL.
	 */ 
	public function __destruct()
	{
		if($this->connectionId)
			mysql_close($this->connectionId);
		$this->connectionId = false;
	}
	
	/**
	 * Gère les requêtes MySQL.
	 * 
	 * Fait la requête, gère les statistiques sur le nombre de requêtes pour la page.
	 * gère les erreurs et les rapports au besoin.
	 * 
	 * Exemple d'utilisation - Exécute une requête et affiche le résultat
	 * <code>
	 * $query = 'SELECT * FROM ' . DB_PREFIX . 'perso WHERE id=801;';
	 * $result = $db->query($query,__FILE__,__LINE__,__FUNCTION__,__CLASS__,__METHOD__);
	 * echo mysql_result($result,0);
	 * </code>
	 *
	 * @see fctBugReport()
	 * @param string $query  Requête MySQL à effectuer
	 * @param string $file En cas d'erreur, permet d'afficher le fichier qui procède à l'appel
	 * @param string $line en cas d'erreur, permet d'afficher la ligne en cause du fichier d'appel
	 * @param string $function en cas d'erreur, permet d'afficher la fonction en cause du fichier d'appel
	 * @param string $class En cas d'erreur, permet d'afficher la classe qui procède à l'appel
	 * @param string $method en cas d'erreur, permet d'afficher la méthode en cause du fichier d'appel
	 * @return Ressource|false Ressource de mysql_query ou false en cas d'échec
	 */ 
	public function query($query, $file=null, $line=null, $function=null, $class=null, $method=null, $byPassCheck=false)
	{
		
		if(!$this->connectionId)
		{
			fctBugReport(
							'Erreur de connexion',
							array(
								'File' => $file,
								'Line' => $line,
								'ErrNo' => 'n/a',
								'Error' => 'Aucune connexion n\'est établie.',
								'query' => $query
							),
							$file,
							$line,
							$function,
							$class,
							$method
						);
		}

		if($this->queryCount>300 && !$byPassCheck)
		{
			fctBugReport(
							'Le système semble être tombé dans une boucle infernale. Arrêt de la page par mesure préventive. Un rapport d\'erreur à été généré.',
							array(
								'File' => $file,
								'Line' => $line,
								'ErrNo' => mysql_errno(),
								'Error' => mysql_error(),
								'query' => $query
							),
							$file,
							$line,
							$function,
							$class,
							$method
						);
		}
		$timerSQL = new Timer();
		$timerSQL->start();
		
		//Effectuer la requete
		$ret = &mysql_query($query,$this->connectionId);
		
		//Mettre à jour les statistiques
		$this->sqlTime += $timerSQL->finish();
		$this->queryCount++;
		
		
		//Si la requête s'est mal déroulée, gérer l'erreur de requête.
		if ($ret === false)
		{
			
			fctBugReport(
							'Erreur de requête',
							array(
								'File' => $file,
								'Line' => $line,
								'ErrNo' => mysql_errno(),
								'Error' => mysql_error(),
								'query' => $query
							),
							$file,
							$line,
							$function,
							$class,
							$method
						);
		}
		
		//Retourner le résultat de la requête
		return $ret;
	}
	
	/**
	 * Retourne l'ID de la connexion MySQL en cours.
	 * 
	 * @return int
	 */
	public function getConnectionId()
	{
		return $this->connectionId;
	}
	
	/**
	 * Retourne le nombre de requêtes effectuées.
	 * 
	 * @return int
	 */
	public function getQueryCount()
	{
		return $this->queryCount;
	}
	
	
	/**
	 * Retourne le temps total pris par les requêtes SQL.
	 *
	 * @return float
	 */
	public function getSqlTime()
	{
		return $this->sqlTime;
	}
}


