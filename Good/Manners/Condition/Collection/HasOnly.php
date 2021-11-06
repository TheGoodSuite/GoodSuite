<?php

namespace Good\Manners\Condition\Collection;

use Good\Manners\CollectionComparisonProcessor;
use Good\Manners\Condition\ComplexCondition;

class HasOnly implements CollectionCondition
{
    private $comparisonOrCondition;

    public function __construct($comparisonOrCondition)
    {
        $this->comparisonOrCondition = $comparisonOrCondition;
    }

    public function processCollectionComparison(CollectionComparisonProcessor $processor)
    {
        if ($this->comparisonOrCondition instanceof ComplexCondition)
        {
            $processor->processHasOnlyConditionComparison($this->comparisonOrCondition);
        }
        else
        {
            $processor->processHasOnlyComparisonComparison($this->comparisonOrCondition);
        }
    }
}

?>
