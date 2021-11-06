<?php

namespace Good\Manners;

interface ComparisonProcessor extends EqualityComparisonProcessor
{
    public function processGreaterThanComparison($value);
    public function processGreaterOrEqualComparison($value);
    public function processLessThanComparison($value);
    public function processLessOrEqualComparison($value);

    public function processAndComparison(Condition $comparison1, Condition $comparison2);
    public function processOrComparison(Condition $comparison1, Condition $comparison2);
}

?>
