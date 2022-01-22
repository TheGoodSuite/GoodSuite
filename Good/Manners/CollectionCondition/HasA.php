<?php

namespace Good\Manners\CollectionCondition;

use Good\Manners\Condition;
use Good\Manners\Condition\EqualTo;
use Good\Manners\CollectionCondition;
use Good\Manners\Processors\CollectionConditionProcessor;
use Good\Manners\Condition\ComplexCondition;
use Good\Service\Type\CollectionType;

class HasA implements CollectionCondition
{
    use TypeValidator;

    private $condition;

    public function __construct($condition)
    {
        if (!$condition instanceof Condition)
        {
            $condition = new EqualTo($condition);
        }

        $this->condition = $condition;
    }

    public function appliesToCollectionType(CollectionType $type)
    {
        return $this->condition->appliesToType($type->getCollectedType());
    }

    public function processCollectionCondition(CollectionConditionProcessor $processor)
    {
        $processor->processHasA($this->condition);
    }

    public function isSatisfiedBy($value)
    {
        $this->validateComparisonCollectionValue($value, 'HasA');

        $result = false;

        foreach ($value as $item)
        {
            $result = $result || $this->condition->isSatisfiedBy($item);
        }

        return $result;
    }
}

?>
