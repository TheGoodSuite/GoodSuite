<?php

namespace Good\Manners\Condition;

use Good\Manners\Storable;
use Good\Manners\Condition;
use Good\Manners\Processors\ConditionProcessor;
use Good\Service\Type;

class EqualTo implements Condition
{
    use TypeValidator;

    private $value;

    public function __construct($value)
    {
        $this->validateForEquality("EqualTo condition", $value);

        $this->value = $value;
    }

    public function appliesToType(Type $type)
    {
        return $type->checkValue($this->value);
    }

    public function processCondition(ConditionProcessor $processor)
    {
        $processor->processEqualToCondition($this->value);
    }

    public function getTargetedReferenceType()
    {
        if ($this->value === null)
        {
            return "*";
        }
        else if ($this->value instanceof Storable)
        {
            return $this->value->getType();
        }
        else
        {
            return null;
        }
    }

    public function isSatisfiedBy($value)
    {
        $this->validateComparisonValue($this->value, $value, 'EqualTo');

        if (($this->value instanceof \DateTimeImmutable) && ($value instanceof \DateTimeImmutable))
        {
            // non-strict checking: we want to know if the values match, not if it's the same object
            return $value == $this->value;
        }
        else if (($this->value instanceof Storable) && ($value instanceof Storable))
        {
            return $this->value->id === $value->id;
        }
        else
        {
            return $value === $this->value;
        }
    }
}

?>
