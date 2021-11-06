<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\ConditionProcessor;

class LessOrEqual implements Condition
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function processCondition(ConditionProcessor $processor)
    {
        $processor->processLessOrEqualCondition($this->value);
    }

    public function getTargetType()
    {
        return null;
    }
}

?>
