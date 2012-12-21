<?php

interface GoodMemoryConditionProcessor
{
	public function processEqualityCondition(GoodMannersStorable $to);
	public function processInequalityCondition(GoodMannersStorable $to);
	public function processGreaterCondition(GoodMannersStorable $to);
	public function processGreaterOrEqualsCondition(GoodMannersStorable $to);
	public function processLessCondition(GoodMannersStorable $to);
	public function processLessOrEqualsCondition(GoodMannersStorable $to);
	
	public function processAndCondition(GoodMannersCondition $condition1, GoodMannersCondition $condition2);
	public function processOrCondition(GoodMannersCondition $condition1, GoodMannersCondition $condition2);
}

?>