<?php

class GoodMemorySQLJoinDiscoverer implements GoodMannersValueVisitor
{
	private $store;
	
	private $currentReference;
	private $currentTable;
	
	public function __construct($store, $currentTable)
	{
		$this->store = $store;
		$this->currentTable = $currentTable;
	}
	
	public function discoverJoins(GoodMannersReferenceValue $value)
	{
		$currentReference = 0;
		
		$value->visitMembers($this);
	}
	
	public function visitReferenceValue(GoodMannersReferenceValue $value)
	{	
		if (!$value->isNull() && $value->isDirty() && $value->getOriginal()->isBlank())
		{
			$join = $this->store->getJoin($this->currentTable, $this->currentReference);
			
			if ($join == -1)
			{
				$join = $this->store->createJoin($this->currentTable, $value->getName(), $this->currentReference, $value->getClassName());
			}
			
			$recursionDiscoverer = new GoodMemorySQLJoinDiscoverer($this->store, $join);
			$recursionDiscoverer->discoverJoins($value);
		}
		
		$currentReference++;
	}
	
	public function visitTextValue(GoodMannersTextValue $value) {}
	public function visitIntValue(GoodMannersIntValue $value) {}
	public function visitFloatValue(GoodMannerwsFloatValue $value) {}
}

?>