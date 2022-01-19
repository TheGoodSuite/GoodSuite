<?php

namespace Good\Manners;

use Good\Manners\Processors\CollectionConditionProcessor;
use Good\Service\Type\CollectionType;

interface CollectionCondition
{
    public function processCollectionCondition(CollectionConditionProcessor $processor);
    public function appliesToCollectionType(CollectionType $type);

    public function isSatisfiedBy($value);
}

?>
