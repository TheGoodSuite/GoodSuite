<?php

namespace Good\Memory;

class BaseCollection
{
	protected $store;
	protected $dbresult;

	public function __construct($store, $dbresult)
	{
		$this->store = $store;
		$this->dbresult = $dbresult;
	}
}

?>