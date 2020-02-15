<?php

namespace Good\Service\Type;

use Good\Service\Type;
use Good\Service\InvalidParameterException;

class FloatType extends \Good\Rolemodel\Schema\Type\FloatType implements Type
{
    public function checkValue($value)
    {
        if ($value === null)
        {
            return;
        }

        if (!is_int($value) && !is_float($value))
        {
            throw new InvalidParameterException("Value specified is not a float");
        }
    }
}

?>
