<?php

class DbMySQL
{
	private $database; 			// string
	private $host;				// string
	private $port;				// int
	private $user;				// string
	private $pass;				// string
	private $prefix;
	private $linkIdentifier;
	
	private $result;			// sql resultset
	
	public function __construct($dbname, $dbhost, $dbport, $dbuser, $dbpass, $dbprefix)
	{
		$this->database = $dbname;
		$this->host = $dbhost;
		$this->port = $dbport;
		$this->user = $dbuser;
		$this->pass = $dbpass;
		$this->prefix = $dbprefix;
		
		$this->linkIdentifier = null;
	}
	
	private function connect()
	{
        $url = $this->host;
        
        if ($this->port !== null)
        {
            $url .= ':' . $this->port;
        }
        
		$this->linkIdentifier = mysql_connect($url, $this->user, $this->pass);
		mysql_select_db($this->database, $this->linkIdentifier);
	}
	
	public function query($query)
	{
		if (!$this->isConnected())
		{
			$this->connect();
		}
		
		$this->result = mysql_query($query, $this->linkIdentifier);
		if ($_GET['showquery'] == 'true')
			echo $query . "; <br /> \n";
	}
	
	public function escape($string)
	{
		if (!$this->isConnected())
		{
			$this->connect();
		}
		
		return mysql_real_escape_string($string, $this->linkIdentifier);
	}
	
	public function getRowAssoc()
	{
		return mysql_fetch_assoc($this->result);
	}
	
	public function getNumRowsInResult()
	{
		return mysql_num_rows($this->result);
	}
    
    public function getNumAffectedRows()
    {
        return mysql_affected_rows($this->linkIdentifier);
    }
    
    public function getLastAutoId()
    {
        return mysql_insert_id($this->linkIdentifier);
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
		return ($this->linkIdentifier != null);
	}
}