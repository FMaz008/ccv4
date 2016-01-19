<?php

class GameException extends Exception
{
	protected $errorUrl;
	
	public function __construct($message, $errorUrl = NULL)
	{
		$this->errorUrl = $errorUrl;
		parent::__construct($message);
	}
	
	public function getErrorUrl()
	{
		return $errorUrl;
	}
}