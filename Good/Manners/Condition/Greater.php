<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\Storable;
use Good\Manners\Store;

class Greater implements Condition
{
	private $to;

	public function __construct(Storable $to)
	{
		$this->to = $to;
	}
	
	public function process(Store $store)
	{
		$store->processGreaterCondition($this->to);
	}
}

?>