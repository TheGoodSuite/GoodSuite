<?php

namespace Good\Manners\Comparison;

use Good\Manners\Comparison;
use Good\Manners\ComparisonProcessor;

class GreaterOrEqual implements Comparison
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function processComparison(ComparisonProcessor $processor)
    {
        $processor->processGreaterOrEqualComparison($this->value);
    }
}

?>
