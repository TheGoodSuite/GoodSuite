<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\ConditionProcessor;
use Good\Manners\ComparisonProcessor;

class NotEqualTo implements Condition
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function processCondition(ConditionProcessor $processor)
    {
    }

    public function processComparison(ComparisonProcessor $processor)
    {
        $processor->processNotEqualToComparison($this->value);
    }

    public function getTargetType()
    {
        return null;
    }
}

?>
