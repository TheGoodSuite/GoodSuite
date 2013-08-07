<?php

namespace Good\Memory\SQL;

use Good\Manners\Storable;
use Good\Manners\StorableVisitor;
use Good\Memory\SQLStorage;

class JoinDiscoverer implements StorableVisitor
{
    private $storage;
    
    private $currentTable;
    
    public function __construct(SQLStorage $storage, $currentTable)
    {
        $this->storage = $storage;
        $this->currentTable = $currentTable;
    }
    
    public function discoverJoins(Storable $value)
    {
        $value->acceptStorableVisitor($this);
    }
    
    public function visitReferenceProperty($name, $datatypeName, $dirty, 
                                                        Storable $value = null)
    {
        
        if ($value !== null && $dirty && $value->isNew())
        {
            $join = $this->storage->getJoin($this->currentTable, $name);
            
            if ($join == -1)
            {
                $join = $this->storage->createJoin($this->currentTable, 
                                                 $name,
                                                 $datatypeName);
            }
            
            $recursionDiscoverer = new JoinDiscoverer($this->storage, $join);
            $recursionDiscoverer->discoverJoins($value);
        }
    }
    
    public function visitTextProperty($name, $dirty, $value) {}
    public function visitIntProperty($name, $dirty, $value) {}
    public function visitFloatProperty($name, $dirty, $value) {}
    public function visitDatetimeProperty($name, $dirty, $value) {}
}

?>