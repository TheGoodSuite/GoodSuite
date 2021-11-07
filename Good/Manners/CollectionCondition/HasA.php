<?php

namespace Good\Manners\CollectionCondition;

use Good\Manners\CollectionCondition;
use Good\Manners\CollectionConditionProcessor;
use Good\Manners\Condition\ComplexCondition;

class HasA implements CollectionCondition
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
            $processor->processHasAConditionCondition($this->comparisonOrCondition);
        }
        else
        {
            $processor->processHasAComparisonCondition($this->comparisonOrCondition);
        }
    }
}

?>
