<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\Processors\ConditionProcessor;

class NotEqualTo implements Condition
{
    use TypeValidator;

    private $value;

    public function __construct($value)
    {
        $this->validateForEquality("NotEqualTo condition", $value);

        $this->value = $value;
    }

    public function processCondition(ConditionProcessor $processor)
    {
        $processor->processNotEqualToCondition($this->value);
    }

    public function getTargetType()
    {
        return null;
    }
}

?>
