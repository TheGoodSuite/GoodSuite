<?php

namespace Good\Manners;

// Quick and dirty fix for namespacing of Good Looking Store
// I should soon make the store non-generated anyway

interface Store
{
	public function createEqualityCondition(Storable $to);
	public function createInequalityCondition(Storable $to);
	public function createGreaterCondition(Storable $to);
	public function createGreaterOrEqualsCondition(Storable $to);
	public function createLessCondition(Storable $to);
	public function createLessOrEqualsCondition(Storable $to);
	
	public function processEqualityCondition(Storable $to);
	public function processInequalityCondition(Storable $to);
	public function processGreaterCondition(Storable $to);
	public function processGreaterOrEqualsCondition(Storable $to);
	public function processLessCondition(Storable $to);
	public function processLessOrEqualsCondition(Storable $to);
	
	public function createAndCondition(Condition $condition1, Condition $condition2);
	public function createOrCondition(Condition $condition1, Condition $condition2);
	
	public function processAndCondition(Condition $condition1, Condition $condition2);
	public function processOrCondition(Condition $condition1, Condition $condition2);
}

?>