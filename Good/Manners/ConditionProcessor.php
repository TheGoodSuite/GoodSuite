<?php

namespace Good\Manners;

interface ConditionProcessor
{
    public function processEqualToCondition(Storable $to);
    public function processNotEqualToCondition(Storable $to);
    public function processGreaterThanCondition(Storable $to);
    public function processGreaterOrEqualCondition(Storable $to);
    public function processLessThanCondition(Storable $to);
    public function processLessOrEqualCondition(Storable $to);
    
    public function processAndCondition(Condition $condition1, Condition $condition2);
    public function processOrCondition(Condition $condition1, Condition $condition2);
}

?>