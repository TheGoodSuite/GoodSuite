<?php

namespace Good\Memory\Database;

require_once 'Result.php';

class MySQLResult
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