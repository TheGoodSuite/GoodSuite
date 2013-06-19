<?php

namespace Good\Manners;

abstract class Condition
{
	abstract protected function doProcess();
	
	private $store;

	protected function __construct(Store $store)
	{
		$this->store = $store;
	}
	
	public function process(Store $store)
	{
		if ($store != $this->store)
		{
			// TODO: turn this into decent error reporting
			throw new \Exception("Store/Condition mismatch: You can only use Conditions created at a Store with that same Store.");
		}
		
		$this->doProcess();
	}
}

?>