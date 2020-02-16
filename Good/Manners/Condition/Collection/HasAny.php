<?php

namespace Good\Manners\Condition\Collection;

use Good\Manners\Condition;
use Good\Manners\ConditionProcessor;

class HasAny implements Condition
{
    private $collection;
    private $condition;

    public function __construct(Collection $collection,
                                Condition $condition)
    {
        $this->collection = $collection;
        $this->condition = $condition;
    }

    public function process(ConditionProcessor $processor)
    {
        // TODO
    }

    public function getTargetType()
    {
        return $this->condition1->getTargetType();
    }
}

?>
