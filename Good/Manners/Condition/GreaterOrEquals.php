<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\ConditionProcessor;
use Good\Manners\Storable;
use Good\Manners\Store;

class GreaterOrEquals implements Condition
{
	private $to;

	public function __construct(Storable $to)
	{
		$this->to = $to;
	}
	
	public function process(ConditionProcessor $store)
	{
		$store->processGreaterOrEqualsCondition($this->to);
	}
}

?>