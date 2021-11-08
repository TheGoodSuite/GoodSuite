<?php

namespace Good\Manners\Processors;

use Good\Manners\Condition\ComplexCondition;
use Good\Manners\Condition;

interface ConditionProcessor
{
    public function processEqualToCondition($value);
    public function processNotEqualToCondition($value);

    public function processGreaterThanCondition($value);
    public function processGreaterOrEqualCondition($value);
    public function processLessThanCondition($value);
    public function processLessOrEqualCondition($value);

    public function processAndCondition(Condition $condition1, Condition $condition2);
    public function processOrCondition(Condition $condition1, Condition $condition2);

    public function processComplexCondition(ComplexCondition $condition);
}

?>
