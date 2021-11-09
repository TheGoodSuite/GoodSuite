<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\Processors\ConditionProcessor;

class LessOrEqual implements Condition
{
    use TypeValidator;

    private $value;

    public function __construct($value)
    {
        $this->validateForComparisons("LessOrEqual condition", $value);

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
