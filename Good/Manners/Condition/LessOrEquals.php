<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;
use Good\Manners\Storable;

class LessOrEquals extends Condition
{
	private $store;
	private $to;

	public function __construct(\Good\Manners\Store $store, Storable $to)
	{
		parent::__construct($store);
		
		$this->store = $store;
		$this->to = $to;
	}
	
	protected function doProcess()
	{
		$this->store->processLessOrEqualsCondition($this->to);
	}
}

?>