<?php

namespace Good\Manners;

interface BasicLogicStore
{
	public function createAndCondition(Condition $condition1, Condition $condition2);
	public function createOrCondition(Condition $condition1, Condition $condition2);
	
	public function processAndCondition(Condition $condition1, Condition $condition2);
	public function processOrCondition(Condition $condition1, Condition $condition2);
}

?>