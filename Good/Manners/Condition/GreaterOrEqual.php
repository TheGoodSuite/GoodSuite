<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\Processors\ConditionProcessor;
use Good\Service\Type;

class GreaterOrEqual implements Condition
{
    use TypeValidator;

    private $value;

    public function __construct($value)
    {
        $this->validateForComparisons("GreaterOrEqual condition", $value);

        $this->value = $value;
    }

    public function appliesToType(Type $type)
    {
        return $type->checkValue($this->value);
    }

    public function processCondition(ConditionProcessor $processor)
    {
        $processor->processGreaterOrEqualCondition($this->value);
    }

    public function getTargetedReferenceType()
    {
        return null;
    }

    public function isSatisfiedBy($value)
    {
        $this->validateComparisonValue($this->value, $value, 'GreaterOrEqual');

        return $value !== null && $value >= $this->value;
    }
}

?>
