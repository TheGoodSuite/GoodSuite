<?php

abstract class GoodMannersCondition
{
	abstract protected function doProcess();
	
	private $store;

	protected function __construct(GoodMannersStore $store)
	{
		$this->store = $store;
	}
	
	public function process(GoodMannersStore $store)
	{
		if ($store != $this->store)
		{
			// TODO: turn this into decent error reporting
			die("Store/Condition mismatch: You can only use Conditions created at a Store with that same Store.");
		}
		
		$this->doProcess();
	}
}

?>