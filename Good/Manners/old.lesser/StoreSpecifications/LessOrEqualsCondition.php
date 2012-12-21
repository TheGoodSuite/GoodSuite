<?php

require_once dirname(__FILE__) . '/../Condition.php';
require_once 'ComparingStore.php';
require_once dirname(__FILE__) . '/../Storable.php';

class GoodMannersLessOrEqualsCondition extends GoodMannerCondition
{
	private $store;
	private $to;

	protected function __construct(GoodMannersComparingStore $store, GoodMannersStorable $to)
	{
		$this->store = $store;
		$this->to = $to;
	}
	
	protected function doProcess()
	{
		$this->store->processLessOrEqualsCondition($this->to);
	}
}

?>