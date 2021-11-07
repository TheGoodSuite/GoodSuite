<?php

namespace Good\Manners;

use Good\Manners\CollectionComparisonProcessor;

interface CollectionCondition
{
    public function processCollectionCondition(CollectionConditionProcessor $processor);
}

?>
