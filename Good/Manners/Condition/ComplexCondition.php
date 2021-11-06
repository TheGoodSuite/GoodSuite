<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\ComplexConditionProcessor;

interface ComplexCondition extends Condition
{
    public function processComplexCondition(ComplexConditionProcessor $processor);
}

?>
