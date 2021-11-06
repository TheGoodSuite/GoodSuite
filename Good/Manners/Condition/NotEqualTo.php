<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\ConditionProcessor;
use Good\Manners\ComparisonProcessor;
use Good\Manners\EqualityComparisonProcessor;

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

    public function processComparison(EqualityComparisonProcessor $processor)
    {
        $processor->processNotEqualToComparison($this->value);
    }

    public function getTargetType()
    {
        return null;
    }
}

?>
