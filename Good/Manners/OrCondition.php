<?php

namespace Good\Manners;

require_once dirname(__FILE__) . '/Condition.php';
require_once 'BasicLogicStore.php';

class OrCondition extends Condition
{
	private $store;
	private $condition1;
	private $condition2;

	public function __construct(BasicLogicStore $store, 
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
		$this->store->processOrCondition($this->condition1, $this->condition2);
	}
}

?>