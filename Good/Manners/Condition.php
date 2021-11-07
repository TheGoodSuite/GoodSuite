<?php

namespace Good\Manners;

use Good\Manners\Processors\ConditionProcessor;

interface Condition
{
    public function processCondition(ConditionProcessor $processor);
    public function getTargetType();
}

?>
