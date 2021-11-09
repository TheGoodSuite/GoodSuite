<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\Processors\ConditionProcessor;
use Good\Manners\CollectionCondition;
use Good\Manners\Processors\CollectionConditionProcessor;
use Good\Service\Type;

class AndCondition implements Condition, CollectionCondition
{
    use TypeValidator;

    private $condition1;
    private $condition2;

    public function __construct($condition1,
                                $condition2)
    {
        $this->validateSubConditions("OrCondition", $condition1, $condition2);

        $this->condition1 = $condition1;
        $this->condition2 = $condition2;
    }

    public function appliesToType(Type $type)
    {
        $problem = $this->condition1->appliesToType($type);

        if ($problem != null)
        {
            return $problem;
        }

        return $this->condition2->appliesToType($type);
    }

    public function processCondition(ConditionProcessor $processor)
    {
        $processor->processAndCondition($this->condition1, $this->condition2);
    }

    public function processCollectionCondition(CollectionConditionProcessor $processor)
    {
        $processor->processAndCollection($this->condition1, $this->condition2);
    }

    public function getTargetType()
    {
        return $this->condition1->getTargetType();
    }
}

?>
