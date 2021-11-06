<?php

namespace Good\Manners;

use Good\Manners\Condition;

interface EqualityComparisonProcessor
{
    public function processEqualToComparison($value);
    public function processNotEqualToComparison($value);

    public function processAndComparison(Condition $comparison1, Condition $comparison2);
    public function processOrComparison(Condition $comparison1, Condition $comparison2);
}

?>
