<?php

namespace Good\Memory;

interface ConditionProcessor
{
	public function processEqualityCondition(\Good\Manners\Storable $to);
	public function processInequalityCondition(\Good\Manners\Storable $to);
	public function processGreaterCondition(\Good\Manners\Storable $to);
	public function processGreaterOrEqualsCondition(\Good\Manners\Storable $to);
	public function processLessCondition(\Good\Manners\Storable $to);
	public function processLessOrEqualsCondition(\Good\Manners\Storable $to);
	
	public function processAndCondition(\Good\Manners\Condition $condition1, \Good\Manners\Condition $condition2);
	public function processOrCondition(\Good\Manners\Condition $condition1, \Good\Manners\Condition $condition2);
}

?>