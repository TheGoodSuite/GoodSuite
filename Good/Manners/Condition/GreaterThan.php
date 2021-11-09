<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\Processors\ConditionProcessor;

class GreaterThan implements Condition
{
    use TypeValidator;

    private $value;

    public function __construct($value)
    {
        $this->validateForComparisons("GreaterThan condition", $value);

        $this->value = $value;
    }

    public function processCondition(ConditionProcessor $processor)
    {
        $processor->processGreaterThanCondition($this->value);
    }

    public function getTargetType()
    {
        return null;
    }
}

?>
