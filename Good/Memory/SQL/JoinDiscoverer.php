<?php

namespace Good\Memory\SQL;

use Good\Manners\Storable;
use Good\Manners\StorableVisitor;

class JoinDiscoverer implements StorableVisitor
{
    private $store;
    
    private $currentTable;
    
    public function __construct($store, $currentTable)
    {
        $this->store = $store;
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
            $join = $this->store->getJoin($this->currentTable, $name);
            
            if ($join == -1)
            {
                $join = $this->store->createJoin($this->currentTable, 
                                                 $name,
                                                 $datatypeName);
            }
            
            $recursionDiscoverer = new JoinDiscoverer($this->store, $join);
            $recursionDiscoverer->discoverJoins($value);
        }
    }
    
    public function visitTextProperty($name, $dirty, $value) {}
    public function visitIntProperty($name, $dirty, $value) {}
    public function visitFloatProperty($name, $dirty, $value) {}
    public function visitDatetimeProperty($name, $dirty, $value) {}
}

?>