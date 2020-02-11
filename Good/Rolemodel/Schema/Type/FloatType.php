<?php

namespace Good\Rolemodel\Schema\Type;

use Good\Rolemodel\TypeVisitor;

class FloatType extends PrimitiveType
{
    public function acceptTypeVisitor(TypeVisitor $visitor)
    {
        // visit this, there are no children to pass visitor on to
        $visitor->visitFloatType($this);
    }

    function getValidParameterTypeModifiers()
    {
        return array();
    }

    function getValidNonParameterTypeModifiers()
    {
        return array();
    }

    function processTypeModifiers(array $typeModifiers)
    {
        return $typeModifiers;
    }

    function getDefaultTypeModifierValues()
    {
        return array();
    }
}

?>
