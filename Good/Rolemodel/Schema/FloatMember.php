<?php

namespace Good\Rolemodel\Schema;

use Good\Rolemodel\SchemaVisitor;

class FloatMember extends PrimitiveMember
{
    public function acceptSchemaVisitor(SchemaVisitor $visitor)
    {
        // visit this, there are no children to pass visitor on to
        $visitor->visitFloatMember($this);
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
