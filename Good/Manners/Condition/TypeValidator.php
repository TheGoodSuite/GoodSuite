<?php

namespace Good\Manners\Condition;

use Good\Manners\Storable;
use Good\Manners\Condition;
use Good\Manners\CollectionCondition;

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

    private function validateSubConditions($conditionName, $value1, $value2)
    {
        if (!($value1 instanceof Condition)
            && !($value1 instanceof CollectionCondition))
        {
            throw new \Exception("Argument for " . $conditionName . " must be either a Condition or a CollectionCondition");
        }

        if (!($value2 instanceof Condition)
            && !($value2 instanceof CollectionCondition))
        {
            throw new \Exception("Argument for " . $conditionName . " must be either a Condition or a CollectionCondition");
        }

        if (!(($value1 instanceof Condition) && ($value2 instanceof Condition))
            && !(($value1 instanceof CollectionCondition) && ($value2 instanceof CollectionCondition)))
        {
            throw new \Exception("Both arguments for " . $conditionName . " must be of the same type");
        }
    }
}

?>
