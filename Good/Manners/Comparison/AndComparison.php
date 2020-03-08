<?php

namespace Good\Manners\Comparison;

use Good\Manners\Comparison;
use Good\Manners\ComparisonProcessor;

class AndComparison implements Comparison
{
    private $comparison1;
    private $comparison2;

    public function __construct(Comparison $comparison1,
                                Comparison $comparison2)
    {
        $this->comparison1 = $comparison1;
        $this->comparison2 = $comparison2;
    }

    public function processComparison(ComparisonProcessor $processor)
    {
        $processor->processAndComparison($this->comparison1, $this->comparison2);
    }
}

?>
