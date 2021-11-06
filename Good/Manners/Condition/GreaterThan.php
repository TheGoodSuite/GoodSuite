<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\ConditionProcessor;

class GreaterThan implements Condition
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function processCondition(ConditionProcessor $processor)
    {
        $processor->processGreaterThanCondition($this->value);
    }

    public function getTargetType()
    {
        return null;
    }
}

?>
