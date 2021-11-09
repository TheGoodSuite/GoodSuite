<?php

namespace Good\Service\Type;

use Good\Service\Type;

class FloatType extends \Good\Rolemodel\Schema\Type\FloatType implements Type
{
    public function checkValue($value)
    {
        if ($value === null)
        {
            return null;
        }

        if (!is_int($value) && !is_float($value))
        {
            return "must be a float";
        }

        return null;
    }
}

?>
