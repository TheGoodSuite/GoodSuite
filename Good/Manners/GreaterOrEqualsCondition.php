<?php

namespace Good\Manners;

class GreaterOrEqualsCondition extends Condition
{
	private $store;
	private $to;

	public function __construct(ComparingStore $store, Storable $to)
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