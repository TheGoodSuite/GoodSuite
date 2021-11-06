<?php

namespace Good\Manners;

use Good\Manners\Condition\Collection\CollectionCondition;
use Good\Service\Type;
use Good\Service\Type\CollectionType;
use Good\Service\Type\ReferenceType;

interface ConditionProcessor
{
    public function processAndCondition(Condition $condition1, Condition $condition2);
    public function processOrCondition(Condition $condition1, Condition $condition2);

    public function processStorableConditionId(Condition $comparison);
    public function processStorableConditionMember(Type $type, $propertyName, Condition $comparison);

    public function processStorableConditionReferenceAsCondition(ReferenceType $type, $propertyName, Condition $condition);
    public function processStorableConditionReferenceAsComparison(ReferenceType $type, $propertyName, Condition $comparison);

    public function processStorableConditionCollection(CollectionType $type, $propertyName, CollectionCondition $comparison);
}

?>
