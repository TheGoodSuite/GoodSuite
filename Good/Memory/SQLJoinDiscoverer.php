<?php

class GoodMemorySQLJoinDiscoverer implements GoodMemoryPropertyVisitor
{
	private $store;
	
	private $currentReference;
	private $currentTable;
	
	public function __construct($store, $currentTable)
	{
		$this->store = $store;
		$this->currentTable = $currentTable;
	}
	
	public function discoverJoins(GoodMannersStorable $value)
	{
		$this->currentReference = 0;
		
		$this->store->setCurrentPropertyVisitor($this);
		$value->acceptStore($this->store);
	}
	
	public function visitReferenceProperty($name, $datatypeName, $dirty, $null, 
														GoodMannersStorable $value = null)
	{
		echo $name, " which is ", $null ? "" : "not ", "null:";
		var_dump($value); var_dump($null);
		if (!$null && $dirty && $value->isNew())
		{
			$join = $this->store->getJoin($this->currentTable, $this->currentReference);
			
			if ($join == -1)
			{
				$join = $this->store->createJoin($this->currentTable, 
												 $name,
												 $this->currentReference,
												 $datatypeName);
			}
			
			$recursionDiscoverer = new GoodMemorySQLJoinDiscoverer($this->store, $join);
			$recursionDiscoverer->discoverJoins($value);
		}
		
		$this->currentReference++;
	}
	
	public function visitTextProperty($name, $dirty, $null, $value) {}
	public function visitIntProperty($name, $dirty, $null, $value) {}
	public function visitFloatProperty($name, $dirty, $null, $value) {}
	public function visitDatetimeProperty($name, $dirty, $null, $value) {}
}

?>