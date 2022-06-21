<?php

namespace Good\Service\Type;

use Good\Service\Type;

class BooleanType extends \Good\Rolemodel\Schema\Type\BooleanType implements Type
{
    public function checkValue($value)
    {
        if ($value === null)
        {
            return null;
        }

        if (!is_bool($value))
        {
            return "must be a boolean";
        }

        return null;
    }
}

?>
