<?php

namespace Good\Manners\Processors;

use Good\Manners\Condition;
use Good\Manners\Condition\ComplexCondition;
use Good\Manners\CollectionCondition;
use Good\Service\Type;
use Good\Service\Type\CollectionType;
use Good\Service\Type\ReferenceType;

interface ComplexConditionProcessor
{
    public function processId(Condition $comparison);
    public function processMember(Type $type, $propertyName, Condition $comparison);

    public function processCollectionMember(CollectionType $type, $propertyName, CollectionCondition $comparison);
}

?>
