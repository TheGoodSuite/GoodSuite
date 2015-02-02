<?php

namespace Good\Rolemodel\Schema;

use Good\Rolemodel\SchemaVisitor;

class TextMember extends PrimitiveMember
{
    public function acceptSchemaVisitor(SchemaVisitor $visitor)
    {
        // visit this, there are no children to pass visitor on to
        $visitor->visitTextMember($this);
    }
    
    function getValidParameterTypeModifiers()
    {
        return array('minLength', 'maxLength', 'length');;
    }
    
    function getValidNonParameterTypeModifiers()
    {
        return array();
    }
    
    function processTypeModifiers(array $typeModifiers)
    {
        if (array_key_exists('length', $typeModifiers))
        {
            if (array_key_exists('minLength', $typeModifiers) ||
                array_key_exists('maxLength', $typeModifiers))
            {
                throw new \Exception("The 'length' type modifier cannot be defined alongside 'minLength' or 'maxLength'.");
            }
            
            $typeModifiers['minLength'] = $typeModifiers['length'];
            $typeModifiers['maxLength'] = $typeModifiers['length'];
            unset($typeModifiers['length']);
        }
        
        return $typeModifiers;
    }
    
    function getDefaultTypeModifierValues()
    {
        return array();
    }
}

?>