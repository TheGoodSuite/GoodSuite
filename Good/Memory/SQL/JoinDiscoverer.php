<?php

namespace Good\Memory\SQL;

use Good\Memory\PropertyVisitor;
use Good\Manners\Storable;

class JoinDiscoverer implements PropertyVisitor
{
	private $store;
	
	private $currentReference;
	private $currentTable;
	
	public function __construct($store, $currentTable)
	{
		$this->store = $store;
		$this->currentTable = $currentTable;
	}
	
	public function discoverJoins(Storable $value)
	{
		$this->currentReference = 0;
		
		$this->store->setCurrentPropertyVisitor($this);
		$value->acceptStore($this->store);
	}
	
	public function visitReferenceProperty($name, $datatypeName, $dirty, 
														Storable $value = null)
	{
		if ($value !== null && $dirty && $value->isNew())
		{
			$join = $this->store->getJoin($this->currentTable, $this->currentReference);
			
			if ($join == -1)
			{
				$join = $this->store->createJoin($this->currentTable, 
												 $name,
												 $this->currentReference,
												 $datatypeName);
			}
			
			$recursionDiscoverer = new JoinDiscoverer($this->store, $join);
			$recursionDiscoverer->discoverJoins($value);
		}
		
		$this->currentReference++;
	}
	
	public function visitTextProperty($name, $dirty, $value) {}
	public function visitIntProperty($name, $dirty, $value) {}
	public function visitFloatProperty($name, $dirty, $value) {}
	public function visitDatetimeProperty($name, $dirty, $value) {}
}

?>