<?php

namespace Good\Manners;

require_once dirname(__FILE__) . '/Condition.php';
require_once dirname(__FILE__) . '/AndCondition.php';
require_once dirname(__FILE__) . '/OrCondition.php';

interface BasicLogicStore
{
	public function createAndCondition(Condition $condition1, Condition $condition2);
	public function createOrCondition(Condition $condition1, Condition $condition2);
	
	public function processAndCondition(Condition $condition1, Condition $condition2);
	public function processOrCondition(Condition $condition1, Condition $condition2);
}

?>