<?php

namespace Good\Rolemodel\Schema;

use Good\Rolemodel\VisitableSchema;

abstract class Member implements VisitableSchema
{
    private $attributes;
    private $name;
    
    abstract public function getReferencedTypeIfAny();
    // each (non-abstract) child also needs to implement accept from Visitable!
    
    private static $knownAttributes = array('server_only', 'private', 'protected', 'public');
    
    public function __construct(array $attributes, $name)
    {
        // Attributes
    
        $this->attributes = $attributes;
        
        // check for unknown attributes
        foreach ($attributes as $attribute)
        {
            if (!\in_array($attribute, self::$knownAttributes))
            {
                // TODO: add a real warning
                
                // WARNING: unknown attribute
            }
        }
        
        // Name
        $this->name = $name;
    }
    
    public function getAttributes()
    {
        return $this->attributes;
    }
    
    public function getName()
    {
        return $this->name;
    }
}

?>