<?php

namespace Good\Memory\Database;

class SQLite implements Database
{
    // because SQLite only supports one "connection" per database,
    // we'll allow reusing the "connection for multiple stores
    private static $dbs = array();
    
    private $filename;
    
    private $db;
    private $result;
    
    public function __construct($filename)
    {
        $this->filename = $filename;
        
        $this->db = null;
        $this->result = null;
    }
    
    private function isConnected()
    {
        return ($this->db != null);
    }
    
    private function connect()
    {
        if (array_key_exists($this->filename, self::$dbs))
        {
            $this->db = self::$dbs[$this->filename];
        }
        else
        {
            $this->db = new \SQLite3($this->filename);
            self::$dbs[$this->filename] = $this->db;
        }
    }
    
    public function query($query)
    {
        if (!$this->isConnected())
        {
            $this->connect();
        }
            
        $this->result = $this->db->query($query);
    }
    
    public function escapeText($string)
    {
        if (!$this->isConnected())
        {
            $this->connect();
        }
        
        return $this->db->escapeString($string);
    }
    
    public function getLastInsertedId()
    {
        return $this->db->lastInsertRowID();
    }
    
    public function getResult()
    {
        return new SQLiteResult($this->result);
    }
}