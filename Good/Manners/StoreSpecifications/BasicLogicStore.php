<?php

require_once dirname(__FILE__) . '/../Condition.php';
require_once dirname(__FILE__) . '/AndCondition.php';
require_once dirname(__FILE__) . '/OrCondition.php';

interface GoodMannersBasicLogicStore
{
	public function createAndCondition(GoodMannersCondition $condition1, GoodMannersCondition $condition2);
	public function createOrCondition(GoodMannersCondition $condition1, GoodMannersCondition $condition2);
	
	public function processAndCondition(GoodMannersCondition $condition1, GoodMannersCondition $condition2);
	public function processOrCondition(GoodMannersCondition $condition1, GoodMannersCondition $condition2);
}

?>