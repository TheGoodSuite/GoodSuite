<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\ConditionProcessor;
use Good\Manners\CollectionCondition;
use Good\Manners\CollectionConditionProcessor;

class AndCondition implements Condition, CollectionCondition
{
    private $condition1;
    private $condition2;

    public function __construct($condition1,
                                $condition2)
    {
        $this->condition1 = $condition1;
        $this->condition2 = $condition2;
    }

    public function processCondition(ConditionProcessor $processor)
    {
        $processor->processAndCondition($this->condition1, $this->condition2);
    }

    public function processCollectionCondition(CollectionConditionProcessor $processor)
    {
        $processor->processAndCollectionCondition($this->condition1, $this->condition2);
    }

    public function getTargetType()
    {
        return $this->condition1->getTargetType();
    }
}

?>
