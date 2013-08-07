<?php

namespace Good\Memory\SQL;

use Good\Memory\Database as Database;

use Good\Memory\SQLStore;
use Good\Memory\SQLPostponedForeignKey;
use Good\Manners\Storable;
use Good\Manners\StorableVisitor;

class Inserter implements StorableVisitor
{
    private $db;
    private $store;
    
    private $sql;
    private $values;
    private $first;
    
    private $inserting;
    private $postponed;
    
    public function __construct(SQLStore $store, Database\Database $db)
    {
        $this->db = $db;
        $this->store = $store;
        $this->postponed = array();
    }
    
    
    public function insert($datatypeName, Storable $value)
    {
        $this->sql = 'INSERT INTO ' . $this->store->tableNamify($datatypeName) . ' (';
        $this->values = 'VALUES (';
        $this->first = true;
        
        $this->inserting = $value;
        
        $value->setNew(false);
        $value->setStore($this->store);
        
        $value->acceptStorableVisitor($this);
        
        $this->sql .= ') ';
        $this->sql .= $this->values . ')';
        
        $this->db->query($this->sql);
        $value->setId($this->db->getLastInsertedId());
        $value->clean();
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
            $this->values .= ', ';
        }
    }
    
    public function getPostponed()
    {
        return $this->postponed;
    }
    
    public function visitReferenceProperty($name, $datatypeName, $dirty, 
                                                        Storable $value = null)
    {
        // If not dirty, do not include field and use default value
        if ($dirty)
        {
            $this->comma();
            
            $this->sql .= $this->store->fieldNamify($name);
        
            if ($value === null)
            {
                $this->values .= 'NULL';
            }
            else
            {
                if ($value->isNew())
                {
                    $inserter = new Inserter($this->store, $this->db);
                    $inserter->insert($datatypeName, $value);
                    $this->postponed = \array_merge($this->postponed, $inserter->getPostponed());
                }
                
                if (!$value->isNew() && $value->getId() == -1)
                // $value is actually new, but not marked as such to prevent infinite recursion
                {
                    $this->postponed[] = new SQLPostponedForeignKey($this->inserting,
                                                                    $name,
                                                                    $value);
                    $this->values .= 'NULL';
                }
                else
                {
                    $this->values .= \intval($value->getId());
                }
            }
        }
    }
    
    public function visitTextProperty($name, $dirty, $value)
    {
        // If not dirty, do not include field and use default value
        if ($dirty)
        {
            $this->comma();
            
            $this->sql .= $this->store->fieldNamify($name);
            
            if ($value === null)
            {
                $this->values .= 'NULL';
            }
            else
            {
                $this->values .= $this->store->parseText($value);
            }
        }
    }
    
    public function visitIntProperty($name, $dirty, $value)
    {
        // If not dirty, do not include field and use default value
        if ($dirty)
        {
            $this->comma();
            
            $this->sql .= $this->store->fieldNamify($name);
            
            if ($value === null)
            {
                $this->values .= 'NULL';
            }
            else
            {
                $this->values .= $this->store->parseInt($value);
            }
        }
    }
    
    public function visitFloatProperty($name, $dirty, $value)
    {
        // If not dirty, do not include field and use default value
        if ($dirty)
        {
            $this->comma();
            
            $this->sql .= $this->store->fieldNamify($name);
        
            if ($value === null)
            {
                $this->values .= 'NULL';
            }
            else
            {
                $this->values .= $this->store->parseFloat($value);
            }
        }
    }
    
    public function visitDatetimeProperty($name, $dirty, $value)
    {
        // If not dirty, do not include field and use default value
        if ($dirty)
        {
            $this->comma();
            
            $this->sql .= $this->store->fieldNamify($name);
        
            if ($value === null)
            {
                $this->values .= 'NULL';
            }
            else
            {
                $this->values .= $this->store->parseDatetime($value);
            }
        }
    }
}

?>