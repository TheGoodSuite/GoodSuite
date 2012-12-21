<?php

class DbPgSQL
{
	private $database; 			// string
	private $host;				// string
	private $port;				// int
	private $user;				// string
	private $pass;				// string
	private $prefix;
	
	private $result;			// sql resultset
    
    private $connection;        // pgsql connection
	
	public function __construct($dbname, $dbhost, $dbport, $dbuser, $dbpass, $dbprefix)
	{
		$this->database = $dbname;
		$this->host = $dbhost;
		$this->port = $dbport;
		$this->user = $dbuser;
		$this->pass = $dbpass;
		$this->prefix = $dbprefix;
		
		$this->connection = null;
	}
	
	private function connect()
	{
        $connString = 'host=' . $this->host;
        
        if ($this->port !== null)
        {
            $connString .= ' port=' . $this->port;
        }
        
        $connString .= ' dbname=' . $this->database . ' user=' . $this->user . 
                                                    ' password=' . $this->pass;
        
		$this->connection = pg_connect($connString);
	}
	
	public function query($query)
	{
        if (!$this->isConnected)
		{
			$this->connect();
		}
		
		$this->result = pg_query($this->connection, $query);
		if ($_GET['showquery'] == 'true')
			echo $query . "; <br /> \n";
	}
	
	public function escape($string)
	{
        if (!$this->isConnected)
		{
			$this->connect();
		}
		
		return pg_escape_string($this->connection, $string);
	}
	
	public function getRowAssoc()
	{
		return pg_fetch_assoc($this->result);
	}
	
	public function getNumRowsInResult()
	{
		return pg_num_rows($this->result);
	}
    
    public function getNumAffectedRows()
    {
        return pg_affected_rows();
    }
    
    public function getLastAutoId()
    {
        $this->query("SELECT lastval()");
        
        if (!$row = $this->getRowAssoc())
        {
            return false;
        }
        
        return $row['lastval'];
    }
    
    public function offsetLimitLine($offset, $limit)
    {
        return 'OFFSET ' . $offset . ' LIMIT ' . $limit;
    }
    
    public function regexToken()
    {
        return '~*';
    }
	
	public function getPrefix()
	{
		return $this->prefix;
	}
	
	private function isConnected()
	{
		$this->connection == null;
	}
}