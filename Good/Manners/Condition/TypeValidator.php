<?php

namespace Good\Manners\Condition;

use Good\Manners\Storable;
use Good\Manners\Condition;
use Good\Manners\CollectionCondition;

trait TypeValidator
{
    private function validateForEquality($conditionName, $value)
    {
        if (!$this->isEquatable($value))
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

    private function isEquatable($value)
    {
        return $this->isPrimitive($value)
            || is_null($value)
            || ($value instanceof Storable);
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

    private function validateComparisonValueForEquality($ownValue, $comparisonValue, $conditionName)
    {
        $this->validateComparisonValue($ownValue, $comparisonValue, $conditionName, true);
    }

    private function validateComparisonValueForAnyComparison($ownValue, $comparisonValue, $conditionName)
    {
        $this->validateComparisonValue($ownValue, $comparisonValue, $conditionName, false);
    }

    private function validateComparisonValue($ownValue, $comparisonValue, $conditionName, $isEqualityComparison)
    {
        if (is_null($ownValue))
        {
            if ($isEqualityComparison)
            {
                if (!$this->isEquatable($comparisonValue))
                {
                    throw new \Exception("Cannot test value of '" . print_r($comparisonValue, true) . "' against " . $conditionName . ": invalid type");
                }
            }
            else
            {
                if (!$this->isPrimitive($comparisonValue))
                {
                    throw new \Exception("Cannot test value of '" . print_r($comparisonValue, true) . "' against " . $conditionName . ": invalid type");
                }
            }
        }
        else
        {
            $sameType = true;

            if ($ownValue instanceof \DateTimeImmutable)
            {
                if ($comparisonValue !== null && !($comparisonValue instanceof \DateTimeImmutable))
                {
                    $sameType = false;
                }
            }
            elseif ($ownValue instanceof Storable)
            {
                if ($comparisonValue !== null && !($comparisonValue instanceof \Storable))
                {
                    $sameType = false;
                }
            }
            else if ($ownValue !== null && $comparisonValue !== null)
            {
                $sameType = gettype($ownValue) === gettype($comparisonValue);
            }

            if (!$sameType)
            {
                throw new \Exception("Cannot test value of '" . print_r($comparisonValue, true) . "' against " . $conditionName . "(" . print_r($ownValue, true) . "): types are not the same");
            }
        }
    }
}

?>
