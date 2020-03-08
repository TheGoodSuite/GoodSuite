<?php

namespace Good\Manners;

use Good\Manners\Comparison;
use Good\Manners\Comparison\EqualityComparison;

interface ConditionProcessor
{
    public function processAndCondition(Condition $condition1, Condition $condition2);
    public function processOrCondition(Condition $condition1, Condition $condition2);

    public function processStorableConditionId(EqualityComparison $comparison);
    public function processStorableConditionDateTime($propertyName, Comparison $comparison);
    public function processStorableConditionFloat($propertyName, Comparison $comparison);
    public function processStorableConditionInt($propertyName, Comparison $comparison);
    public function processStorableConditionText($propertyName, Comparison $comparison);

    public function processStorableConditionReferenceAsCondition($propertyName, $datatypeName, Condition $condition);
    public function processStorableConditionReferenceAsComparison($propertyName, EqualityComparison $comparison);
}

?>
