<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\ConditionProcessor;
use Good\Manners\ComparisonProcessor;

class AndCondition implements Condition
{
    private $condition1;
    private $condition2;

    public function __construct(Condition $condition1,
                                Condition $condition2)
    {
        $this->condition1 = $condition1;
        $this->condition2 = $condition2;
    }

    public function processCondition(ConditionProcessor $processor)
    {
        $processor->processAndCondition($this->condition1, $this->condition2);
    }

    public function processComparison(ComparisonProcessor $processor)
    {
        $processor->processAndComparison($this->condition1, $this->condition2);
    }

    public function getTargetType()
    {
        return $this->condition1->getTargetType();
    }
}

?>
