<?php

namespace Good\Manners;

interface Condition
{
    public function process(ConditionProcessor $processor);
}

?>