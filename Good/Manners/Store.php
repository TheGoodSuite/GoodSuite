<?php

namespace Good\Manners;

// Quick and dirty fix for namespacing of Good Looking Store
// I should soon make the store non-generated anyway

interface Store
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