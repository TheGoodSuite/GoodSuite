<?php

namespace Good\Manners\Processors;

use Good\Manners\Condition;
use Good\Manners\CollectionCondition;

interface CollectionConditionProcessor
{
    public function processHasA(Condition $condition);
    public function processHasOnly(Condition $condition);

    public function processAndCollection(CollectionCondition $comparison1, CollectionCondition $comparison2);
    public function processOrCollection(CollectionCondition $comparison1, CollectionCondition $comparison2);
}

?>
