<?php

namespace Good\Manners\Comparison\Collection;

use Good\Manners\CollectionComparisonProcessor;

class HasOnly implements CollectionComparison
{
    private $comparisonOrCondition;

    public function __construct($comparisonOrCondition)
    {
        $this->comparisonOrCondition = $comparisonOrCondition;
    }

    public function processCollectionComparison(CollectionComparisonProcessor $processor)
    {
        if ($this->comparisonOrCondition instanceof Condition)
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
