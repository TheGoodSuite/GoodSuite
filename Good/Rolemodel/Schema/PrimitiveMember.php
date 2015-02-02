<?php

namespace Good\Rolemodel\Schema;

abstract class PrimitiveMember extends Member
{
    private $typeModifiers;
    
    public function __construct(array $attributes, $name, array $typeModifiers)
    {
        parent::__construct($attributes, $name);
        
        $this->typeModifiers = $typeModifiers;
    }
    
    public function getReferencedTypeIfAny()
    {
        return null;
    }
    
    public function getTypeModifiers()
    {
        return $this->typeModifiers;
    }
}

?>