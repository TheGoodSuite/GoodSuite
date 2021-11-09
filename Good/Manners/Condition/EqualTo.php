<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\Processors\ConditionProcessor;

class EqualTo implements Condition
{
    use TypeValidator;

    private $value;

    public function __construct($value)
    {
        $this->validateForEquality("EqualTo condition", $value);

        $this->value = $value;
    }

    public function processCondition(ConditionProcessor $processor)
    {
        $processor->processEqualToCondition($this->value);
    }

    public function getTargetType()
    {
        return null;
    }
}

?>
