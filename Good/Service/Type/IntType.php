<?php

namespace Good\Service\Type;

use Good\Service\Type;
use Good\Service\InvalidParameterException;

class IntType extends \Good\Rolemodel\Schema\Type\IntType implements Type
{
    public function checkValue($value)
    {
        if ($value === null)
        {
            return;
        }

        if (!is_int($value))
        {
            throw new InvalidParameterException("Value specified is not an int");
        }

        if ($value < $this->getTypeModifiers()['minValue'])
        {
            throw new InvalidParameterException("Value specified is below the minimum value for this field");
        }

        if ($value > $this->getTypeModifiers()['maxValue'])
        {
            throw new InvalidParameterException("Value specified is above the maximum value for this field");
        }
    }
}

?>
