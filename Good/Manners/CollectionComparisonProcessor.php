<?php

namespace Good\Manners;

interface CollectionComparisonProcessor
{
    public function processHasAComparisonComparison(Comparison $comparison);
    public function processHasAConditionComparison(Condition $condition);
    public function processHasOnlyComparisonComparison(Comparison $comparison);
    public function processHasOnlyConditionComparison(Condition $condition);
}

?>
