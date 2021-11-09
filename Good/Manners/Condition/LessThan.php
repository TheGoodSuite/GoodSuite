<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\Processors\ConditionProcessor;

class LessThan implements Condition
{
    use TypeValidator;

    private $value;

    public function __construct($value)
    {
        $this->validateForComparisons("LessThan condition", $value);

        $this->value = $value;
    }

    public function processCondition(ConditionProcessor $processor)
    {
        $processor->processLessThanCondition($this->value);
    }

    public function getTargetType()
    {
        return null;
    }
}

?>
