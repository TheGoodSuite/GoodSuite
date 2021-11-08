<?php

namespace Good\Manners\Processors;

use Good\Manners\Condition;
use Good\Manners\CollectionCondition;

interface CollectionConditionProcessor
{
    public function processHasA(Condition $condition);
    public function processHasOnly(Condition $condition);

    public function processAndCollection(CollectionCondition $condition1, CollectionCondition $condition2);
    public function processOrCollection(CollectionCondition $condition1, CollectionCondition $condition2);
}

?>
