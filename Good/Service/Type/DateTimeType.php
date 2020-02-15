<?php

namespace Good\Service\Type;

use Good\Service\Type;
use Good\Service\InvalidParameterException;

class DateTimeType extends \Good\Rolemodel\Schema\Type\DateTimeType implements Type
{
    public function checkValue($value)
    {
        if ($value === null)
        {
            return;
        }

        if (!($value instanceof \DateTimeImmutable))
        {
            throw new InvalidParameterException("Value specified is not a DateTimeImmutable");
        }
    }
}

?>
