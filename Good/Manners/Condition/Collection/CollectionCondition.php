<?php

namespace Good\Manners\Condition\Collection;

use Good\Manners\CollectionComparisonProcessor;

interface CollectionCondition
{
    public function processCollectionComparison(CollectionComparisonProcessor $processor);
}

?>
