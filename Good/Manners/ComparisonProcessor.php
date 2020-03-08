<?php

namespace Good\Manners;

interface ComparisonProcessor extends EqualityComparisonProcessor
{
    public function processGreaterThanComparison($value);
    public function processGreaterOrEqualComparison($value);
    public function processLessThanComparison($value);
    public function processLessOrEqualComparison($value);

    public function processAndComparison(Comparison $comparison1, Comparison $comparison2);
    public function processOrComparison(Comparison $comparison1, Comparison $comparison2);
}

?>
