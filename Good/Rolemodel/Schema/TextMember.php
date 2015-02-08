<?php

namespace Good\Rolemodel\Schema;

use Good\Rolemodel\SchemaVisitor;
use Good\Rolemodel\InvalidTypeModifierException;

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
                throw new InvalidTypeModifierException("The 'length' type modifier cannot be defined alongside 'minLength' or 'maxLength'.");
            }
            
            if ($typeModifiers['length'] < 0)
            {
                throw new InvalidTypeModifierException("The length for a text must be positive");
            }
            
            $typeModifiers['minLength'] = $typeModifiers['length'];
            $typeModifiers['maxLength'] = $typeModifiers['length'];
            unset($typeModifiers['length']);
        }
        
        if (array_key_exists('minLength', $typeModifiers) && 
            array_key_exists('maxLength', $typeModifiers) &&
            $typeModifiers['minLength'] > $typeModifiers['maxLength'])
        {
            throw new InvalidTypeModifierException("The minLength for a text cannot be higher than its maxLength");
        }
        
        if ((array_key_exists('minLength', $typeModifiers) && $typeModifiers['minLength'] < 0) || 
            (array_key_exists('maxLength', $typeModifiers) && $typeModifiers['maxLength'] < 0))
        {
            throw new InvalidTypeModifierException("The minLength and maxLength for a text must be positive");
        }
        
        return $typeModifiers;
    }
    
    function getDefaultTypeModifierValues()
    {
        return array('minLength' => 0);
    }
}

?>