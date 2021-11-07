<?php

namespace Good\Manners\CollectionCondition;

use Good\Manners\Condition;
use Good\Manners\Condition\EqualTo;
use Good\Manners\CollectionCondition;
use Good\Manners\Processors\CollectionConditionProcessor;
use Good\Manners\Condition\ComplexCondition;

class HasA implements CollectionCondition
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
