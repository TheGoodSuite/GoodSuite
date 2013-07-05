<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\Storable;

class Equality extends Condition
{
	private $store;
	private $to;

	public function __construct(\Good\Manners\ComparingStore $store, Storable $to)
	{
		parent::__construct($store);
		
		$this->store = $store;
		$this->to = $to;
	}
	
	protected function doProcess()
	{
		$this->store->processEqualityCondition($this->to);
	}
}

?>