<?php

namespace Good\Manners;

interface CollectionComparisonProcessor
{
    public function processHasAComparisonComparison(Condition $comparison);
    public function processHasAConditionComparison(Condition $condition);
    public function processHasOnlyComparisonComparison(Condition $comparison);
    public function processHasOnlyConditionComparison(Condition $condition);
}

?>
