<?php

class PDOPlusStatement extends PDOStatement
{
	public $dbh;
	
	protected $p_params;
	
	
	
	protected function __construct($dbh)
	{
		$this->dbh = $dbh;
		$this->p_params = array();
		
	}
	
	public function executePlus(&$pdoInstance = NULL, $file=NULL, $line=NULL)
	{
		$try = 0;
		while($try < 10)
		{
			try
			{
				if($pdoInstance===NULL)
				{
					return parent::execute();
				}
				else
				{
					$pdoInstance->startTimer();
					$sql = parent::execute();
					$pdoInstance->stopTimer();
					
					return $sql;
				}
			}
			catch(Exception $e)
			{
				// 1213 = ER_LOCK_DEADLOCK : code : '40001'
				// 1205 = ER_LOCK_WAIT_TIMEOUT : code : 'HY000'
				if(($e->getCode() != '40001' && $e->getCode() != 'HY000') || $try >= 9)
				{
					fctBugReport(
							'Erreur de requête',
							array(
								'error code' => $e->getCode(),
								'error' => $e->getMessage(),
								'requete' => $this->queryString,
								'values' => $this->p_params,
								'try' => $try
							),
							$file,
							$line
						);
				}
			}
			$try++;
		}
	}
	
	/*
	public function bindParam ( $parameter, $variable, $data_type = NULL, $length = NULL, $driver_options = array())
	{
		if( is_bool($variable) && $data_type == PDO::PARAM_INT )
			$variable = intval($variable);
		
		$param 						= array();
		$param["parameter"] 		= $parameter;
		$param["variable"] 			= $variable;
		$param["data_type"] 		= $data_type;
		$param["length"] 			= $length;
		$param["driver_options"] 	= $driver_options;
		$this->p_params[] 			= $param;
		
		return parent::bindParam($parameter,$variable,$data_type,$length,$driver_options);
	}*/

	
	public function bindValue ( $parameter, $variable, $data_type = NULL)
	{
		if( is_bool($variable) && $data_type == PDO::PARAM_INT )
			$variable = intval($variable);
		
		$param 						= array();
		$param["parameter"] 		= $parameter;
		$param["variable"] 			= $variable;
		$param["data_type"] 		= $data_type;
		$this->p_params[] 			= $param;
		
		return parent::bindValue($parameter,$variable,$data_type);
	}
	/*
	public function debugQuery()
	{
		
		$retour = $this->queryString;
		foreach( $this->p_params as $param )
		{
			switch( $param["data_type"] )
			{
				case PDO::PARAM_STR:
					$pattern 		= '`('.preg_quote("".$param["parameter"]).')([\s]{0,}[,]{0,})`i';
					$replacement 	= "'".($param["variable"])."'$2";
					$tmp			= $retour;
					$tmp = preg_replace($pattern, $replacement, $tmp , 1 );
					if( $retour == $tmp )
					{
						echo "pattern : ".$pattern."\n";
						echo "retour : ".$retour."\n";
					}
					$retour = $tmp;
				break;
				default:
					$pattern 		= '`('.preg_quote("".$param["parameter"]).')([\s]{0,}[,]{0,})`i';
					$replacement 	= "".($param["variable"])."$2";
					$tmp			= $retour;
					$tmp = preg_replace($pattern, $replacement, $tmp , 1);
					if( $retour == $tmp )
					{
						echo "pattern : ".$pattern."\n";
						echo "retour : ".$retour."\n";
					}
					$retour = $tmp;
					
				break;
			}
		}
		return $retour;
	}
	*/
}

