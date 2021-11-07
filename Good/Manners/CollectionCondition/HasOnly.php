<?php

namespace Good\Manners\CollectionCondition;

use Good\Manners\CollectionCondition;
use Good\Manners\CollectionConditionProcessor;
use Good\Manners\Condition\ComplexCondition;

class HasOnly implements CollectionCondition
{
    private $comparisonOrCondition;

    public function __construct($comparisonOrCondition)
    {
        $this->comparisonOrCondition = $comparisonOrCondition;
    }

    public function processCollectionCondition(CollectionConditionProcessor $processor)
    {
        if ($this->comparisonOrCondition instanceof ComplexCondition)
        {
            $processor->processHasOnlyConditionCondition($this->comparisonOrCondition);
        }
        else
        {
            $processor->processHasOnlyComparisonCondition($this->comparisonOrCondition);
        }
    }
}

?>
