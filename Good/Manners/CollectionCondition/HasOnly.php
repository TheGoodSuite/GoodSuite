<?php

namespace Good\Manners\CollectionCondition;

use Good\Manners\Condition;
use Good\Manners\Condition\EqualTo;
use Good\Manners\CollectionCondition;
use Good\Manners\Processors\CollectionConditionProcessor;
use Good\Manners\Condition\ComplexCondition;

class HasOnly implements CollectionCondition
{
    private $comparisonOrCondition;

    public function __construct($comparisonOrCondition)
    {
        if (!$comparisonOrCondition instanceof Condition)
        {
            $comparisonOrCondition = new EqualTo($comparisonOrCondition);
        }

        $this->comparisonOrCondition = $comparisonOrCondition;
    }

    public function processCollectionCondition(CollectionConditionProcessor $processor)
    {
        $processor->processHasOnly($this->comparisonOrCondition);
    }
}

?>
