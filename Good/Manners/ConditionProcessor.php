<?php

namespace Good\Manners;

use Good\Manners\Condition\ComplexCondition;

interface ConditionProcessor
{
    public function processEqualToCondition($value);
    public function processNotEqualToCondition($value);

    public function processGreaterThanCondition($value);
    public function processGreaterOrEqualCondition($value);
    public function processLessThanCondition($value);
    public function processLessOrEqualCondition($value);

    public function processAndCondition(Condition $comparison1, Condition $comparison2);
    public function processOrCondition(Condition $comparison1, Condition $comparison2);

    public function processComplexCondition(ComplexCondition $condition);
}

?>
