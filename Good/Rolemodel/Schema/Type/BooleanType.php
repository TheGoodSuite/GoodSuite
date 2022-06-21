<?php

namespace Good\Rolemodel\Schema\Type;

use Good\Rolemodel\TypeVisitor;

class BooleanType extends PrimitiveType
{
    public function acceptTypeVisitor(TypeVisitor $visitor)
    {
        // visit this, there are no children to pass visitor on to
        $visitor->visitBooleanType($this);
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
