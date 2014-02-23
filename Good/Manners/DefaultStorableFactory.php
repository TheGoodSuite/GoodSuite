<?php

namespace Good\Manners;

class DefaultStorableFactory implements StorableFactory
{
    private $registeredTypes = array();
    
    public function createStorable($type)
    {
        if (array_key_exists($type, $this->registeredTypes))
        {
            return new $this->registeredTypes[$type];
        }
        else
        {
            return new $type;
        }
    }
    
    public function registerType($parentType, $childType)
    {
        if (!is_subclass_of($childType, $parentType) && $childType != $parentType)
        {
            throw new \Exception("Tried to register class that does not satisfy type requirement.");
        }
        
        $this->registeredTypes[$parentType] = $childType;
    }
}

?>