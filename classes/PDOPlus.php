<?php
/**
 * Gestion de la connexion MySQL.
 *
 * Cette classe s'occupe de la gestion des accès PDO
 *
 * @author Francois Mazerolle <admin@maz-concept.com> 
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.1
 * @package CyberCity_2034
 */

class PDOPlus extends PDO
{
	
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
	private $queryTime;
	
	/**
	 * Timer du calcul des temps requête
	 *
	 * @var object
	 * @access private
	 */
	private $timer;

	
	/**
	 * Calcul le nombre de transactions actives
	 *
	 * @var bool
	 * @access private
	 */
	private $activeTransactionCount = 0;


   
	public function __construct($dsn, $username="", $password="", $driver_options=array() )
	{
		$this->timer = new Timer();
		$this->queryCount = 0;
		$this->queryTime = 0;
		
		parent::__construct($dsn,$username,$password, $driver_options);
		$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('PDOPlusStatement', array($this)));
	   
	}
	
	public function prepare($query, $file=null, $line=null)
	{
		try
		{
			$this->startTimer();
			$sql = parent::prepare($query);
			$this->stopTimer(false);
			
			return $sql;
		}
		catch(Exception $e)
		{
			fctBugReport(
						'Erreur de requête',
						array(
							'error' => $e->getMessage(),
							'query' => $query
						),
						$file,
						$line
					);
		}
	}
	
	

	
	public function exec($sql, $file=null, $line=null)
	{
		try
		{
			$this->startTimer();
			$sql = parent::exec($sql);
			$this->stopTimer();

			return $sql;
		}
		catch(Exception $e)
		{
			fctBugReport(
						'Erreur de requête',
						array(
							'error' => $e->getMessage(),
							'query' => $query
						),
						$file,
						$line
					);
		}
	}



	public function query($query, $file=null, $line=null, $function=null, $class=null, $method=null, $byPassCheck=false)
	{
		try
		{
			$this->startTimer();
			$sql = parent::query($query);
			$this->stopTimer();

			return $sql;
		}
		catch(Exception $e)
		{
			fctBugReport(
						'Erreur de requête',
						array(
							'error' => $e->getMessage(),
							'query' => $query
						),
						$file,
						$line
					);
		}
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
	 * Augmente le temps total des requêtes effectuées.
	 */
	public function startTimer()
	{
		$this->timer->start();
	}
	/**
	 * Augmente le temps total des requêtes effectuées.
	 */
	public function stopTimer($addQueryCount=true)
	{
		$this->queryTime += $this->timer->finish();
		if($addQueryCount)
			$this->queryCount++;
	}
	
	/**
	 * Retourne le temps total pris par les requêtes SQL.
	 *
	 * @return float
	 */
	public function getQueryTime()
	{
		return $this->queryTime;
	}







	//Gestion avancé des transactions
	// Sert à gérer les cas de:
	// beginTransaction();
	// 		beginTransaction(); //Ne rien faire
	//		commit(); 			//Ne rien faire
	// commit();

	public function beginTransaction()
	{
		$this->activeTransactionCount++;
		if ( $this->activeTransactionCount > 1)
			return false;
		else
			return parent::beginTransaction ();
   }

	public function commit()
	{
		if($this->activeTransactionCount == 1)
			parent::commit ();
		
		$this->activeTransactionCount--;
   }

	public function rollback()
	{
		parent::rollback();
		$this->activeTransactionCount = 0;
	}
	
}
