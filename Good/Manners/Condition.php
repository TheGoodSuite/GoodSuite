<?php

namespace Good\Manners;

interface Condition
{
    public function processCondition(ConditionProcessor $processor);
    public function processComparison(ComparisonProcessor $processor);
    public function getTargetType();
}

?>
