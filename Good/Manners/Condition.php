<?php

namespace Good\Manners;

use Good\Manners\Processors\ConditionProcessor;
use Good\Service\Type;

interface Condition
{
    public function processCondition(ConditionProcessor $processor);
    public function appliesToType(Type $type);
    public function getTargetType();
}

?>
