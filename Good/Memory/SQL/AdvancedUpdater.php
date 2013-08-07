<?php

namespace Good\Memory\SQL;

use Good\Memory\Database;
use Good\Memory\SQLStorage;
use Good\Manners\Storable;
use Good\Manners\StorableVisitor;
use Good\Manners\Condition;

class AdvancedUpdater implements StorableVisitor
{
    private $db;
    private $storage;
    
    private $subquery;
    
    private $sql;
    private $first;
    private $currentTable;
    
    private $condition;
    private $rootTableName;
    
    public function __construct(SQLStorage $storage, Database\Database $db, $currentTable)
    {
        $this->db = $db;
        $this->storage = $storage;
        $this->currentTable = $currentTable;
    }
    
    public function update($datatypeName, Condition $condition, 
                            Storable $value)
    {
        $this->updateWithRootTableName($datatypeName, $condition, $value, $datatypeName);
    }
    
    public function updateWithRootTableName($datatypeName, Condition $condition, 
                                                    Storable $value, $rootTableName)
    {
        $this->condition = $condition;
        $this->rootTableName = $rootTableName;
    
        $joinDiscoverer = new JoinDiscoverer($this->storage, 0);
        $joinDiscoverer->discoverJoins($value);
        
        $this->sql = 'UPDATE ' . $this->storage->tableNamify($datatypeName);
        $this->sql .= ' SET ';
        
        $this->first = true;
        $value->acceptStorableVisitor($this);
        
        // if we haven't got a single entry to update, we don't do anything
        // (there is no reason for alarm, though, it may just be that this
        //  table is only used in the ON clause)
        if (!$this->first)
        {
            $conditionWriter = new UpdateConditionWriter($this->storage, 0);
            
            $conditionWriter->writeCondition($condition, $rootTableName, $this->currentTable, $datatypeName);
            
            $this->sql .= ' WHERE ' . $conditionWriter->getCondition();
            
            
            $this->db->query($this->sql);
        }
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
        if ($dirty)
        {
            if ($value !== null && $value->isNew())
            {
                $join = $this->storage->getJoin($this->currentTable, $name);
                
                $updater = new AdvancedUpdater($this->storage, $this->db, $join);
                $updater->updateWithRootTableName($datatypeName, $this->condition, 
                                                                $value, $this->rootTableName);
            }
            else
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
                    $this->sql .= intval($value->getId());
                }
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