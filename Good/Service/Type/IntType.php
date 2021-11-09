<?php

namespace Good\Service\Type;

use Good\Service\Type;

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
            return "must be an int";
        }

        if ($value < $this->getTypeModifiers()['minValue'])
        {
            return "value is below the minimum value for this field";
        }

        if ($value > $this->getTypeModifiers()['maxValue'])
        {
            return "Value specified is above the maximum value for this field";
        }

        return null;
    }
}

?>
