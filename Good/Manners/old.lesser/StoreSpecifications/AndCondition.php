<?php

require_once dirname(__FILE__) . '/../Condition.php';
require_once 'BasicLogicStore.php';

class GoodMannersAndCondition extends Condition
{
	private $store;
	private $condition1;
	private $condition2;

	protected function __construct(GoodMannersBasicLogicStore $store, 
								    GoodMannersCondition $condition1, 
								     GoodMannersCondition $condition2)
	{
		$this->store = $store;
		$this->condition1 = $condition1;
		$this->condition2 = $condition2;
	}
	
	protected function doProcess()
	{
		$this->store->processAndCondition($this->condition1, $this->condition2);
	}
}

?>