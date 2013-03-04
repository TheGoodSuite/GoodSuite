<?php

namespace Good\Memory;

require_once dirname(__FILE__) . '/Database/Result.php';


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