<?php

namespace Good\Manners\CollectionCondition;

use Good\Manners\Condition;
use Good\Manners\Condition\EqualTo;
use Good\Manners\CollectionCondition;
use Good\Manners\Processors\CollectionConditionProcessor;
use Good\Manners\Condition\ComplexCondition;

class HasA implements CollectionCondition
{
    private $condition;

    public function __construct($condition)
    {
        if (!$condition instanceof Condition)
        {
            $condition = new EqualTo($condition);
        }

        $this->condition = $condition;
    }

    public function processCollectionCondition(CollectionConditionProcessor $processor)
    {
        $processor->processHasA($this->condition);
    }
}

?>
