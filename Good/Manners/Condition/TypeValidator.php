<?php

namespace Good\Manners\Condition;

use Good\Manners\Storable;

trait TypeValidator
{
    private function validateForEquality($conditionName, $value)
    {
        if (!$this->isPrimitive($value)
            && !is_null($value)
            && !($value instanceof Storable))
        {
            throw new \Exception("Invalid value for " . $conditionName);
        }
    }

    private function validateForComparisons($conditionName, $value)
    {
        if (!$this->isPrimitive($value))
        {
            throw new \Exception("Invalid value for " . $conditionName);
        }
    }

    private function isPrimitive($value)
    {
        return is_float($value)
            || is_int($value)
            || is_string($value)
            || $value instanceof \DateTimeImmutable;
    }
}

?>
