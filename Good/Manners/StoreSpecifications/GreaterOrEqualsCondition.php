<?php

require_once dirname(__FILE__) . '/../Condition.php';
require_once 'ComparingStore.php';
require_once dirname(__FILE__) . '/../Storable.php';

class GoodMannersGreaterOrEqualsCondition extends GoodMannersCondition
{
	private $store;
	private $to;

	public function __construct(GoodMannersComparingStore $store, GoodMannersStorable $to)
	{
		parent::__construct($store);
		
		$this->store = $store;
		$this->to = $to;
	}
	
	protected function doProcess()
	{
		$this->store->processGreaterOrEqualsCondition($this->to);
	}
}

?>