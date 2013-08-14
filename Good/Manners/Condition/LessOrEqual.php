<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\ConditionProcessor;
use Good\Manners\Storable;

class LessOrEqual implements Condition
{
    private $to;

    public function __construct(Storable $to)
    {
        $this->to = $to;
    }
    
    public function process(ConditionProcessor $processor)
    {
        $processor->processLessOrEqualCondition($this->to);
    }
}

?>