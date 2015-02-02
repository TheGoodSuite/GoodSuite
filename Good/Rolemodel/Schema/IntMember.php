<?php

namespace Good\Rolemodel\Schema;

use Good\Rolemodel\SchemaVisitor;

class IntMember extends PrimitiveMember
{
    
    public function acceptSchemaVisitor(SchemaVisitor $visitor)
    {
        // visit this, there are no children to pass visitor on to
        $visitor->visitIntMember($this);
    }
    
    function getValidParameterTypeModifiers()
    {
        return array('minValue', 'maxValue');
    }
    
    function getValidNonParameterTypeModifiers()
    {
        return array('nonNegative');
    }
    
    function processTypeModifiers(array $typeModifiers)
    {
        return $typeModifiers;
    }
    
    function getDefaultTypeModifierValues()
    {
        // standard int values, also (practically) guaranteed range of php ints
        return array('minValue' => -2147483648, 'maxValue' => 2147483647);
    }
}

?>