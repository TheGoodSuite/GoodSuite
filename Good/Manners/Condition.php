<?php

namespace Good\Manners;

interface Condition
{
    public function processCondition(ConditionProcessor $processor);
    public function getTargetType();
}

?>
