<?php

require_once dirname(__FILE__) . '/../Storable.php';
require_once dirname(__FILE__) . '/EqualityCondition.php';
require_once dirname(__FILE__) . '/InequalityCondition.php';
require_once dirname(__FILE__) . '/GreaterCondition.php';
require_once dirname(__FILE__) . '/GreaterOrEqualsCondition.php';
require_once dirname(__FILE__) . '/LessCondition.php';
require_once dirname(__FILE__) . '/LessOrEqualsCondition.php';

interface GoodMannersComparingStore
{
	public function createEqualityCondition(GoodMannersStorable $to);
	public function createInequalityCondition(GoodMannersStorable $to);
	public function createGreaterCondition(GoodMannersStorable $to);
	public function createGreaterOrEqualsCondition(GoodMannersStorable $to);
	public function createLessCondition(GoodMannersStorable $to);
	public function createLessOrEqualsCondition(GoodMannersStorable $to);
	
	public function processEqualityCondition(GoodMannersStorable $to);
	public function processInequalityCondition(GoodMannersStorable $to);
	public function processGreaterCondition(GoodMannersStorable $to);
	public function processGreaterOrEqualsCondition(GoodMannersStorable $to);
	public function processLessCondition(GoodMannersStorable $to);
	public function processLessOrEqualsCondition(GoodMannersStorable $to);
}

?>