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
        if ($value === null)
        {
            return "*";
        }
        else if ($value instanceof Storable)
        {
            return $value->getType();
        }
        else
        {
            return null;
        }
    }
}

?>
