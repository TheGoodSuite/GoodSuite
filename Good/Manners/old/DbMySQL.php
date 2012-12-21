<?php

class DbMySQL
{
	private $databaseName;  	// string
	private $host;				// string
	private $port;				// int
	private $user;				// string
	private $pass;				// string
	private $prefix;
    
	private $db;
	
	private $result;			// mysqli_result object
	
	public function __construct($dbname, $dbhost, $dbport, $dbuser, $dbpass, $dbprefix)
	{
		$this->database = $dbname;
		$this->host = $dbhost;
		$this->port = $dbport;
		$this->user = $dbuser;
		$this->pass = $dbpass;
		$this->prefix = $dbprefix;
		
		$this->database = null;
        $this->result = null;
	}
	
	private function connect()
	{
        $url = $this->host;
        
        if ($this->port !== null)
        {
            $this->port = ini_get("mysqli.default_port");
        }
        
		$this->db = new MySQLi($this->host, $this->user, $this->pass, 
                                                    $this->database, $this->port);
	}
	
	public function query($query)
	{
		if (!$this->isConnected())
		{
			$this->connect();
		}
		
		$this->result = $this->db->query($query);
		if ($_GET['showquery'] == 'true')
			echo $query . "; <br /> \n";
	}
	
	public function escape($string)
	{
		if (!$this->isConnected())
		{
			$this->connect();
		}
		
		$this->db->escape_string($string);
	}
	
	public function getRowAssoc()
	{
		return $this->result->fetch_assoc();
	}
	
	public function getNumRowsInResult()
	{
		return $this->result->$num_rows;
	}
    
    public function getNumAffectedRows()
    {
        return $this->db->affected_rows;
    }
    
    public function getLastAutoId()
    {
        return $this->db->$insert_id;
    }
    
    public function offsetLimitLine($offset, $limit)
    {
        return 'LIMIT ' . $offset . ', ' . $limit;
    }
    
    public function regexToken()
    {
        return 'REGEXP';
    }
	
	public function getPrefix()
	{
		return $this->prefix;
	}
	
	private function isConnected()
	{
		return ($this->database != null);
	}
}