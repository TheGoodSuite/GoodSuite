<?php

require_once dirname(__FILE__) . '/Database/DatabaseResult.php';


class GoodMemoryBaseCollection
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