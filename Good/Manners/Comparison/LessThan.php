<?php

namespace Good\Manners\Comparison;

use Good\Manners\Comparison;
use Good\Manners\ComparisonProcessor;

class LessThan implements Comparison
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function processComparison(ComparisonProcessor $processor)
    {
        $processor->processLessThanComparison($this->value);
    }
}

?>
