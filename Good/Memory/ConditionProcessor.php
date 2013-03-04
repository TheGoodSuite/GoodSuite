<?php

namespace Good\Memory;

use Good\Manners\Storable;
use Good\Manners\Condition;

interface ConditionProcessor
{
	public function processEqualityCondition(Storable $to);
	public function processInequalityCondition(Storable $to);
	public function processGreaterCondition(Storable $to);
	public function processGreaterOrEqualsCondition(Storable $to);
	public function processLessCondition(Storable $to);
	public function processLessOrEqualsCondition(Storable $to);
	
	public function processAndCondition(Condition $condition1, Condition $condition2);
	public function processOrCondition(Condition $condition1, Condition $condition2);
}

?>