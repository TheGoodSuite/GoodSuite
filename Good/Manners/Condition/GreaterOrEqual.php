<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\Processors\ConditionProcessor;

class GreaterOrEqual implements Condition
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function processCondition(ConditionProcessor $processor)
    {
        $processor->processGreaterOrEqualCondition($this->value);
    }

    public function getTargetType()
    {
        return null;
    }
}

?>
