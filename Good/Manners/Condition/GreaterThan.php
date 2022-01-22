<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\Processors\ConditionProcessor;
use Good\Service\Type;

class GreaterThan implements Condition
{
    use TypeValidator;

    private $value;

    public function __construct($value)
    {
        $this->validateForComparisons("GreaterThan condition", $value);

        $this->value = $value;
    }

    public function appliesToType(Type $type)
    {
        return $type->checkValue($this->value);
    }

    public function processCondition(ConditionProcessor $processor)
    {
        $processor->processGreaterThanCondition($this->value);
    }

    public function getTargetedReferenceType()
    {
        return null;
    }

    public function isSatisfiedBy($value)
    {
        $this->validateComparisonValueForAnyComparison($this->value, $value, 'GreaterThan');

        return $value !== null && $value > $this->value;
    }
}

?>
