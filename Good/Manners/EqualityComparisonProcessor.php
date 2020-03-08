<?php

namespace Good\Manners;

use Good\Manners\Comparison\EqualityComparison;

interface EqualityComparisonProcessor
{
    public function processEqualToComparison($value);
    public function processNotEqualToComparison($value);

    public function processAndComparison(EqualityComparison $comparison1, EqualityComparison $comparison2);
    public function processOrComparison(EqualityComparison $comparison1, EqualityComparison $comparison2);
}

?>
