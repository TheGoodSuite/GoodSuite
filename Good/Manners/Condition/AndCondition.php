<?php

namespace Good\Manners\Condition;

use Good\Manners\Condition;

class AndCondition extends Condition
{
	private $store;
	private $condition1;
	private $condition2;

	public function __construct(\Good\Manners\Store $store, 
								    Condition $condition1, 
								     Condition $condition2)
	{
		parent::__construct($store);
		
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