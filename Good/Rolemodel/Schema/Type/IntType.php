<?php

namespace Good\Rolemodel\Schema\Type;

use Good\Rolemodel\TypeVisitor;
use Good\Rolemodel\InvalidTypeModifierException;

class IntType extends PrimitiveType
{

    public function acceptTypeVisitor(TypeVisitor $visitor)
    {
        // visit this, there are no children to pass visitor on to
        $visitor->visitIntType($this);
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
        if (array_key_exists('minValue', $typeModifiers) &&
            array_key_exists('maxValue', $typeModifiers) &&
            $typeModifiers['minValue'] > $typeModifiers['maxValue'])
        {
            throw new InvalidTypeModifierException("The minValue for an int cannot be higher than its maxValue");
        }

        if (array_key_exists('nonNegative', $typeModifiers))
        {
            if ((array_key_exists('minValue', $typeModifiers) && $typeModifiers['minValue'] < 0) ||
                (array_key_exists('maxValue', $typeModifiers) && $typeModifiers['maxValue'] < 0))
            {
                throw new InvalidTypeModifierException("If nonNegative is set on an int, its minValue and maxValue can't be negative");
            }

            if (!array_key_exists('minValue', $typeModifiers))
            {
                $typeModifiers['minValue'] = 0;
            }

            unset($typeModifiers['nonNegative']);
        }

        return $typeModifiers;
    }

    function getDefaultTypeModifierValues()
    {
        // standard int values, also (practically) guaranteed range of php ints
        return array('minValue' => -2147483648, 'maxValue' => 2147483647);
    }
}

?>
