<?php

namespace Good\Manners;

interface CollectionConditionProcessor
{
    public function processHasAComparisonCondition(Condition $comparison);
    public function processHasAConditionCondition(Condition $condition);
    public function processHasOnlyComparisonCondition(Condition $comparison);
    public function processHasOnlyConditionCondition(Condition $condition);

    public function processAndCollectionCondition(CollectionCondition $comparison1, CollectionCondition $comparison2);
    public function processOrCollectionCondition(CollectionCondition $comparison1, CollectionCondition $comparison2);
}

?>
