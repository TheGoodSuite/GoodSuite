<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\Processors\ConditionProcessor;
use Good\Service\Type;

class LessOrEqual implements Condition
{
    use TypeValidator;

    private $value;

    public function __construct($value)
    {
        $this->validateForComparisons("LessOrEqual condition", $value);

        $this->value = $value;
    }

    public function appliesToType(Type $type)
    {
        return $type->checkValue($this->value);
    }

    public function processCondition(ConditionProcessor $processor)
    {
        $processor->processLessOrEqualCondition($this->value);
    }

    public function getTargetedReferenceType()
    {
        return null;
    }

    public function isSatisfiedBy($value)
    {
        return $value !== null && $value <= $this->value;
    }
}

?>
