<?php

namespace Good\Manners;

use Good\Manners\Comparison;
use Good\Manners\Comparison\Collection\CollectionComparison;
use Good\Manners\Comparison\EqualityComparison;
use Good\Service\Type;
use Good\Service\Type\CollectionType;
use Good\Service\Type\ReferenceType;

interface ConditionProcessor
{
    public function processAndCondition(Condition $condition1, Condition $condition2);
    public function processOrCondition(Condition $condition1, Condition $condition2);

    public function processStorableConditionId(EqualityComparison $comparison);
    public function processStorableConditionMember(Type $type, $propertyName, Comparison $comparison);

    public function processStorableConditionReferenceAsCondition(ReferenceType $type, $propertyName, Condition $condition);
    public function processStorableConditionReferenceAsComparison(ReferenceType $type, $propertyName, EqualityComparison $comparison);

    public function processStorableConditionCollection(CollectionType $type, $propertyName, CollectionComparison $comparison);
}

?>
