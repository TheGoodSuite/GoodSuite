<?php

namespace Good\Memory;

use Good\Manners\Storage;
use Good\Manners\Storable;
use Good\Manners\Condition;
use Good\Manners\Resolver;

class SQLStorage extends Storage
{
    private $db;
    
    private $joins = array();
    private $joinsReverse = array();
    private $numberOfJoins = 0;
    
    private $dirties = array();
    
    private $postponed = array();
    
    public function __construct($db)
    {
        parent::__construct();
    
        $this->db = $db;
    }
    
    public function insert(Storable $storable)
    {
        $this->dirties[] = $storable;
    }
    
    public function modifyAny(Condition $condition, Storable $modifications)
    {
        if (count($this->dirties) > 0)
        {
            $this->flush();
        }
        
        $this->joins = array(0 => array());
        $this->numberOfJoins = 0;
        
        $updater = new SQL\AdvancedUpdater($this, $this->db, 0);
        
        $updater->update($modifications->getType(), $condition, $modifications);
    }
    
    public function getCollection(Condition $condition, Resolver $resolver)
    {
        $this->joins = array(0 => array());
        $this->numberOfJoins = 0;
        
        $selecter = new SQL\Selecter($this, $this->db, 0);
        
        // I can't really do this on the next line, where I create the collection, since
        // I also use something that as a side effect of this function call.
        // (I don't know if php guarantees the order in which arguments are calculated,
        //  but even if it does, it makes for confusing code to rely on it)
        $resultset = $selecter->select($resolver->getType(), $condition, $resolver);
        
        return new StorableCollection($this, $resultset, $this->joins, $resolver->getType());
    }
    
    public function dirtyStorable(Storable $storable)
    {
        $this->dirties[] = $storable;
    }
    
    private $flushing =  false;
    private $reflush = false;
    
    public function flush()
    {
        if ($this->flushing)
        {
            $this->reflush = true;
            return;
        }
        
        $this->flushing = true;
        
        // Sort all the Storables in $this->dirties
        $deleted = array();
        $modified = array();
        $new = array();
        
        foreach ($this->dirties as $dirty)
        {
            if ($dirty->isDeleted() && !$dirty->isNew())
            {
                $deleted[] = $dirty;
            }
            else if ($dirty->isNew() && !$dirty->isDeleted())
            {
                $new[] = $dirty;
            }
            else if (!$dirty->isNew())
            {
                $modified[] = $dirty;
            }
        }
        
        $this->dirties = array();
        
        if (count($new) > 0)
        {
            $inserter = new SQL\Inserter($this, $this->db);
            
            foreach ($new as $storable)
            {
                // We check again if it is new, as it might already be inserted when resolving dependencies
                // of another insert, in which case it is not new anymore.
                if ($storable->isNew())
                {
                    $inserter->insert($storable->getType(), $storable);
                }
            }
            
            $allPostponed = $inserter->getPostponed();
            
            foreach ($allPostponed as $postponed)
            {
                $postponed->doNow();
            }
            
            if (count($allPostponed) > 0)
            {
                $this->flush();
            }
        }
        
        if (count($modified) > 0)
        {
            $updater = new SQL\SimpleUpdater($this, $this->db);
            
            foreach ($modified as $storable)
            {
                $updater->update($storable->getType(), $storable);
                $storable->clean();
            }
        }
        
        if (count($deleted) > 0)
        {
            foreach ($deleted as $storable)
            {
                $sql  = 'DELETE FROM ' . $this->tableNamify($storable->getType());
                $sql .= " WHERE id = " . intval($storable->getId());
                
                $this->db->query($sql);
            }
        }
        
        $this->flushing = false;
        
        if ($this->reflush)
        {
            $this->reflush = false;
            $this->flush();
        }
    }
    
    public function tableNamify($value)
    {
        return \strtolower($value);
    }
    
    public function fieldNamify($value)
    {
        return \strtolower($value);
    }
    
    public function parseInt($value)
    {
        return \intval($value);
    }
    
    public function parseFloat($value)
    {
        return \floatval($value);
    }
    
    public function parseDatetime($value)
    {
        // shouldn't be necessary when we do stricter type checking,
        // but let's just stick with it for now.
        if (!($value instanceof \DateTime))
        {
            // TODO: turn this into real error reporting
            throw new \Exception("Non-DateTime given for a DateTime field.");
        }
        
        return "'" . $value->format('Y-m-d H:i:s') . "'";
    }
    
    public function parseText($value)
    {
        return "'" . $this->db->escapeText($value) . "'";
    }
    
    public function getJoin($table, $field)
    {
        if (\array_key_exists($this->fieldNamify($field), $this->joins[$table]))
        {
            return $this->joins[$table][$this->fieldNamify($field)]->tableNumberDestination;
        }
        else
        {
            return -1;
        }
    }
    
    public function getReverseJoin($tableNumber)
    {
        if (\array_key_exists($tableNumber, $this->joinsReverse))
        {
            return $this->joinsReverse[$tableNumber];
        }
        else
        {
            return null;
        }
    }
    
    public function getJoins()
    {
        return $this->joins;
    }
    
    public function createJoin($tableNumberOrigin, $fieldNameOrigin, $tableNameDestination)
    {
        // we start off with increment because joins index is numberOfJoins + 1 (index 0 is for base table)
        $this->numberOfJoins++;
        
        $join = new SQL\Join($tableNumberOrigin,
                             $fieldNameOrigin,
                             $tableNameDestination,
                             $this->numberOfJoins);
        
        // We fieldnamify the key here because it's the most flexible
        // It means you can get the join if you have the non fieldnamified type
        // (you can just call fieldnamify on it) as well as when you do have the
        // fieldnamified type.
        $this->joins[$tableNumberOrigin][$this->fieldNamify($fieldNameOrigin)] = $join;
        
        $this->joins[$this->numberOfJoins] = array();
        $this->joinsReverse[$this->numberOfJoins] = $join;
        
        return $this->numberOfJoins;
    }
    
    public function createStorable(array $data, $joins, $type, $tableNumber = 0, &$nextTable = 0, $dataPreparsed = false)
    {
        if (!$dataPreparsed)
        {
            $parsed = array();
            
            foreach ($data as $field => $value)
            {
                $parsed[strstr($field, '_', true)][substr($field, strpos($field, '_') + 1)] = $value;
            }
            
            $data = $parsed;
        }
        
        $table = 't' . $tableNumber;
        $nextTable++;
        
        if (array_key_exists($data[$table]["id"], 
        // todo: allow for proper checking again, after solving the problems that brought with it.
        // note: the following line has changed since it was commenting out.
        //          it'll probably need a second array_key_exists when it is uncommented again.
        //       (on ($type, $this->created))
        //                                                    $this->created[$type]))' . "\n";
        // for now, we just concoct something that will always evaluate to false
                                                            array()))
        {
            return $this->created[$type][$data[$table]["id"]];
        }
        
        $storableData = array();
        
        foreach ($data[$table] as $field => $value)
        {
            if (array_key_exists($field, $joins[$tableNumber]))
            {
                // this is a resolved reference
                // unresolved referenced are completely absent, and
                // non-references aren't in the joins table
                
                if ($value === null)
                {
                    $storableData[$field] = null;
                    $nextTable++;
                }
                else
                {
                    $storableData[$field] = $this->createStorable($data, 
                                                                  $joins,
                                                                  $joins[$tableNumber][$field]->tableNameDestination,
                                                                  $nextTable,
                                                                  $nextTable,
                                                                  true);
                }
            }
            else
            {
                // non-reference
                $storableData[$field] = $value;
            }
        }
        
        $ret = $this->storableFactory->createStorable($type);
        
        $denamifier = new FieldDenamifier($this);
        $storableData = $denamifier->denamifyFields($storableData, $ret);
        
        $ret->setFromArray($storableData);
        $ret->setId($data[$table]["id"]);
        
        $ret->setNew(false);
        $ret->clean();
        $ret->setStorage($this);
        
        $this->created[$type][$data[$table]["id"]] = $ret;
        
        return $ret;
    }
}

?>