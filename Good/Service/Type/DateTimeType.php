<?php

namespace Good\Service\Type;

use Good\Service\Type;

class DateTimeType extends \Good\Rolemodel\Schema\Type\DateTimeType implements Type
{
    public function checkValue($value)
    {
        if ($value === null)
        {
            return null;
        }

        if (!($value instanceof \DateTimeImmutable))
        {
            return "must be a  DateTimeImmutable";
        }

        return null;
    }
}

?>
