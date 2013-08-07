<?php

namespace Good\Memory\Database;

class MySQL implements Database
{
    private $databaseName;      // string
    private $host;                // string
    private $port;                // int
    private $user;                // string
    private $pass;                // string
    private $prefix;
    
    private $db;
    private $result;
    
    public function __construct($dbname, $dbhost, $dbport, $dbuser, $dbpass, $dbprefix)
    {
        $this->database = $dbname;
        $this->host = $dbhost;
        $this->port = $dbport;
        $this->user = $dbuser;
        $this->pass = $dbpass;
        $this->prefix = $dbprefix;
        
        $this->db = null;
        $this->result = null;
    }
    
    private function isConnected()
    {
        return ($this->db != null);
    }
    
    private function connect()
    {
        if ($this->port == null)
        {
            $this->port = \ini_get("mysqli.default_port");
        }
        
        $this->db = new \MySQLi($this->host, $this->user, $this->pass, 
                                                    $this->database, $this->port);
    }
    
    public function query($query)
    {
        if (!$this->isConnected())
        {
            $this->connect();
        }
        
        if (isset($_GET['showquery']) && $_GET['showquery'] == 'true')
            echo $query . "; <br /> \n";
        
        if (isset($_GET['doquery']) && $_GET['doquery'] == 'no')
            return;
            
        $this->result = $this->db->query($query);
    }
    
    public function escapeText($string)
    {
        if (!$this->isConnected())
        {
            $this->connect();
        }
        
        return $this->db->real_escape_string($string);
    }
    
    public function getLastInsertedId()
    {
        return $this->db->insert_id;
    }
    
    public function getResult()
    {
        return new MySQLResult($this->result);
    }
    
    /*
    public function getNumRowsInResult()
    {
        return $this->result->$num_rows;
    }
    
    public function getNumAffectedRows()
    {
        return $this->db->affected_rows;
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
    */
}