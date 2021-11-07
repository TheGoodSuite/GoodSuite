<?php

namespace Good\Manners;

use Good\Manners\Processors\CollectionConditionProcessor;

interface CollectionCondition
{
    public function processCollectionCondition(CollectionConditionProcessor $processor);
}

?>
