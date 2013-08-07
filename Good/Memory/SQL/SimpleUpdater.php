<?php

namespace Good\Memory\SQL;

use Good\Memory\Database as Database;

use Good\Memory\SQLStorage;
use Good\Manners\Storable;
use Good\Manners\StorableVisitor;

class SimpleUpdater implements StorableVisitor
{
    private $db;
    private $storage;
    
    private $sql;
    private $first;
    
    public function __construct(SQLStorage $storage, Database\Database $db)
    {
        $this->db = $db;
        $this->storage = $storage;
    }
    
    
    public function update($datatypeName, Storable $value)
    {
        $this->sql = 'UPDATE ' . $this->storage->tableNamify($datatypeName);
        $this->sql .= ' SET ';
        
        $this->first = true;
        $value->acceptStorableVisitor($this);
        
        $this->sql .= " WHERE id = " . intval($value->getId()) . "";
        
        $this->db->query($this->sql);
    }

    private function comma()
    {
        if ($this->first)
        {
            $this->first = false;
        }
        else
        {
            $this->sql .= ', ';
        }
    }
    
    public function visitReferenceProperty($name, $datatypeName, $dirty, 
                                                            Storable $value = null)
    {
        // We don't need to recurse, because if the value is dirty as well,
        // the storage knows it and will get to updating it by itself
        if ($dirty)
        {
            $this->comma();
            
            $this->sql .= $this->storage->fieldNamify($name);
            $this->sql .= ' = ';
        
            if ($value === null)
            {
                $this->sql .= 'NULL';
            }
            else
            {
                $this->sql .= \intval($value->getId());
            }
        }
    }
    
    public function visitTextProperty($name, $dirty, $value)
    {
        if ($dirty)
        {
            $this->comma();
            
            $this->sql .= $this->storage->fieldNamify($name);
            $this->sql .= ' = ';
            
            if ($value === null)
            {
                $this->sql .= 'NULL';
            }
            else
            {
                $this->sql .= $this->storage->parseText($value);
            }
        }
    }
    
    public function visitIntProperty($name, $dirty, $value)
    {
        if ($dirty)
        {
            $this->comma();
            
            $this->sql .= $this->storage->fieldNamify($name);
            $this->sql .= ' = ';
            
            if ($value === null)
            {
                $this->sql .= 'NULL';
            }
            else
            {
                $this->sql .= $this->storage->parseInt($value);
            }
        }
    }
    
    public function visitFloatProperty($name, $dirty, $value)
    {
        if ($dirty)
        {
            $this->comma();
            
            $this->sql .= $this->storage->fieldNamify($name);
            $this->sql .= ' = ';
            
            if ($value === null)
            {
                $this->sql .= 'NULL';
            }
            else
            {
                $this->sql .= $this->storage->parseFloat($value);
            }
        }
    }
    
    public function visitDatetimeProperty($name, $dirty, $value)
    {
        if ($dirty)
        {
            $this->comma();
            
            $this->sql .= $this->storage->fieldNamify($name);
            $this->sql .= ' = ';
            
            if ($value === null)
            {
                $this->sql .= 'NULL';
            }
            else
            {
                $this->sql .= $this->storage->parseDatetime($value);
            }
        }
    }
}

?>