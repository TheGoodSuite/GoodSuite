<?php

namespace Good\Manners\Comparison\Collection;

use Good\Manners\CollectionComparisonProcessor;
use Good\Service\Type;

class HasA implements CollectionComparison
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
            $processor->processHasAConditionComparison($this->comparisonOrCondition);
        }
        else
        {
            $processor->processHasAComparisonComparison($this->comparisonOrCondition);
        }
    }
}

?>
