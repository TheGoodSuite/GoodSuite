<?php

require_once 'DatabaseResult.php';

class GoodMemoryDbMySQLResult
{
	private $result;
	
	public function __construct($result)
	{
		$this->result = $result;
	}
	
	public function fetch()
	{
		return $this->result->fetch_assoc();
	}
}