<?php

namespace Good\Memory;

use Good\Manners\Condition;
use Good\Manners\Condition\EqualTo;
use Good\Manners\Condition\ComplexCondition;
use Good\Manners\Processors\ConditionProcessor;
use Good\Manners\Processors\ComplexConditionProcessor;
use Good\Manners\Storable;
use Good\Service\Type\IntType;
use Good\Service\Type;

class CollectionOwnerCondition implements ComplexCondition
{
    private $collectedType;
    private $condition;

    public function __construct(Storable $owner)
    {
        $this->owner = $owner;
    }

    public function processCondition(ConditionProcessor $processor)
    {
        $processor->processComplexCondition($this);
    }

    public function processComplexCondition(ComplexConditionProcessor $processor)
    {
        $processor->processMember(new IntType([], "owner"), "owner", new EqualTo($this->owner->id));
    }

    public function appliesToType(Type $type)
    {
        return true;
    }

    public function getTargetedReferenceType()
    {
        return null;
    }

    public function isSatisfiedBy($value)
    {
        return null;
    }
}

?>
