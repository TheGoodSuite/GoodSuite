<?php

namespace Good\Manners;

class GreaterCondition extends Condition
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
		$this->store->processGreaterCondition($this->to);
	}
}

?>