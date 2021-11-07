<?php

namespace Good\Memory\SQL;

use Good\Manners\Processors\ConditionProcessor;
use Good\Manners\Condition;
use Good\Manners\Condition\ComplexCondition;

class ComplexConditionDiscoverer implements ConditionProcessor
{
    private $complexCondition;

    public function discoverComplexCondition(Condition $condition)
    {
        $this->complexCondition = null;

        $condition->processCondition($this);

        return $this->complexCondition;
    }

    public function processEqualToCondition($value) {}
    public function processNotEqualToCondition($value) {}

    public function processGreaterThanCondition($value) {}
    public function processGreaterOrEqualCondition($value) {}
    public function processLessThanCondition($value) {}
    public function processLessOrEqualCondition($value) {}

    public function processAndCondition(Condition $comparison1, Condition $comparison2) {}
    public function processOrCondition(Condition $comparison1, Condition $comparison2) {}

    public function processComplexCondition(ComplexCondition $condition)
    {
        $this->complexCondition = $condition;
    }
}

?>
