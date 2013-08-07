<?php

namespace Good\Memory\SQL;

use Good\Memory\Database as Database;

use Good\Memory\SQLStorage;
use Good\Manners\Condition;
use Good\Manners\Resolver;
use Good\Manners\ResolverVisitor;

class Selecter implements ResolverVisitor
{
    private $db;
    private $storage;
    
    private $subquery;
    
    private $sql;
    private $currentTable;
    
    private $order = array();
    
    public function __construct(SQLStorage $storage, Database\Database $db, $currentTable)
    {
        $this->db = $db;
        $this->storage = $storage;
        $this->currentTable = $currentTable;
    }
    
    
    public function select($datatypeName, Condition $condition, Resolver $resolver)
    {
        $this->sql = "SELECT t0.id AS t0_id";
        
        $resolver->acceptResolverVisitor($this);
        
        $this->sql .= $this->writeQueryWithoutSelect($datatypeName, $condition);
        
        $this->db->query($this->sql);
        
        return $this->db->getResult();
    }
    
    public function writeQueryWithoutSelect($datatypeName, 
                                            Condition $condition)
    {
        $sql  = " FROM " . $this->storage->tableNamify($datatypeName) . " AS t0";
        
        $conditionWriter = new ConditionWriter($this->storage, 0);
        $conditionWriter->writeCondition($condition);
        
        foreach ($this->storage->getJoins() as $somejoins)
        {
            foreach ($somejoins as $join)
            {
                $sql .= ' LEFT JOIN ' . $this->storage->tableNamify($join->tableNameDestination) . 
                                                            ' AS t' . $join->tableNumberDestination;
                $sql .= ' ON t' . $join->tableNumberOrigin . '.' . 
                                            $this->storage->fieldNamify($join->fieldNameOrigin);
                $sql .= ' = t' . $join->tableNumberDestination . '.id';
            }
        }
        
        $sql .= ' WHERE ' . $conditionWriter->getCondition();
        
        // Code below can't simply be replaced by a foreach or implode,
        // because that will happen in the order the entries are created
        // and we want to use the numerical indices as order.
        // One could use "ksort", but I believe this is more efficient
        // in most cases.
        for ($i = 0; $i < \count($this->order); $i++)
        {
            if ($i == 0)
            {
                $sql .= ' ORDER BY ' . $this->order[$i];
            }
            else
            {
                $sql .= ', ' . $this->order[$i];
            }
        }
        
        return $sql;
    }
    
    public function resolverVisitResolvedReferenceProperty($name, $datatypeName, Resolver $resolver)
    {
        if ($resolver == null)
        {
            // resolver should only be null if resolved is false
            // just checking here (maybe this should throw an error,
            // but I'd say it's only a flaw in Good not outside it 
            // that can trigger this)
            throw new \Exception();
        }
        
        $this->sql .= ', ';
        $this->sql .= 't' . $this->currentTable . '.' . $this->storage->fieldNamify($name);
        $this->sql .= ' AS t' . $this->currentTable . '_' . $this->storage->fieldNamify($name);
    
        $join = $this->storage->getJoin($this->currentTable, $name);
        
        if ($join == -1)
        {
            $join = $this->storage->createJoin($this->currentTable,
                                               $name,
                                               $datatypeName);
        }
                
        $this->sql .= ', ';
        $this->sql .= 't' . $join . '.id AS t' . $join . '_id';
        
        $currentTable = $this->currentTable;
        $this->currentTable = $join;
        $resolver->acceptResolverVisitor($this);
        $this->currentTable = $currentTable;
    }
    
    public function resolverVisitUnresolvedReferenceProperty($name)
    {
    }
    
    public function resolverVisitNonReferenceProperty($name)
    {
        $this->sql .= ', ';
        
        $this->sql .= 't' . $this->currentTable . '.' . $this->storage->fieldNamify($name);
        $this->sql .= ' AS t' . $this->currentTable . '_' . $this->storage->fieldNamify($name);

    }
    
    public function resolverVisitOrderAsc($number, $name)
    {
        $this->order[$number] = 't' . $this->currentTable . '_' . 
                        $this->storage->fieldnamify($name) . ' ASC';
    }
    
    public function resolverVisitOrderDesc($number, $name)
    {
        $this->order[$number] = 't' . $this->currentTable . '_' . 
                        $this->storage->fieldnamify($name) . ' DESC';
    }
}

?>