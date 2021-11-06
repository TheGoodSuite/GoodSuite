<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\ConditionProcessor;

class LessThan implements Condition
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function processCondition(ConditionProcessor $processor)
    {
        $processor->processLessThanCondition($this->value);
    }

    public function getTargetType()
    {
        return null;
    }
}

?>
