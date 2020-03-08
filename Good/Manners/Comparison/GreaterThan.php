<?php

namespace Good\Manners\Comparison;

use Good\Manners\Comparison;
use Good\Manners\ComparisonProcessor;

class GreaterThan implements Comparison
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function processComparison(ComparisonProcessor $processor)
    {
        $processor->processGreaterThanComparison($this->value);
    }
}

?>
