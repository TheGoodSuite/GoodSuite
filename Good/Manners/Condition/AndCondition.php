<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\Processors\ConditionProcessor;
use Good\Manners\CollectionCondition;
use Good\Manners\Processors\CollectionConditionProcessor;
use Good\Service\Type;
use Good\Service\Type\CollectionType;

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
        if (!($this->condition1 instanceof Condition))
        {
            throw new \Exception("Can Only use appliesToType if using Conditions");
        }

        $problem = $this->condition1->appliesToType($type);

        if ($problem != null)
        {
            return $problem;
        }

        return $this->condition2->appliesToType($type);
    }

    public function appliesToCollectionType(CollectionType $type)
    {
        if (!($this->condition1 instanceof CollectionCondition))
        {
            throw new \Exception("Can Only use appliesToCollectionType if using CollectionConditions");
        }

        $problem = $this->condition1->appliesToCollectionType($type);

        if ($problem != null)
        {
            return $problem;
        }

        return $this->condition2->appliesToCollectionType($type);
    }

    public function processCondition(ConditionProcessor $processor)
    {
        $processor->processAndCondition($this->condition1, $this->condition2);
    }

    public function processCollectionCondition(CollectionConditionProcessor $processor)
    {
        $processor->processAndCollection($this->condition1, $this->condition2);
    }

    public function getTargetedReferenceType()
    {
        $reference1 = $this->condition1->getTargetedReferenceType();
        $reference2 = $this->condition2->getTargetedReferenceType();

        if (!(   $reference1 == $reference2
              || ($reference1 == "*" && $reference2 !== null)
              || ($reference1 !== null && $reference1 == "*")
           ))
        {
            throw new \Exception("Incompatible types in AndCondition");
        }

        if ($reference1 == "*")
        {
            return $reference2;
        }
        else
        {
            return $reference1;
        }
    }
}

?>
