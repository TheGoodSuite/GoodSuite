<?php

namespace Good\Manners;

interface ComparingStore
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
}

?>