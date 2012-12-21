<?php

require_once dirname(__FILE__) . '/../Manners/Report/ValueVisitor.php';

class GoodMemorySQLSimpleUpdater implements GoodMannersValueVisitor
{
	private $db;
	private $store;
	
	private $sql;
	private $first;
	
	public function __construct(SQLStore $store, GoodMemoryDatabase $db)
	{
		$this->db = $db;
		$this->store = $store;
	}
	
	
	public function update(GoodMannersReferenceValue $value)
	{
		$this->sql = 'UPDATE ' . $this->store->tableNamify($value->getClassName());
		$this->sql .= ' SET ';
		
		$value->visitMembers($this);
		
		$this->sql .= " WHERE id = " . intval($value->getOriginal()->getId()) . "";
		
		$this->db->query($this->sql);
	}

	private function comma()
	{
		if ($this->first)
		{
			$this->first = false;
		}
		else
		{
			$sql .= ', ';
		}
	}
	
	public function visitReferenceValue(GoodMannersReferenceValue $value)
	{
		// We don't need to recurse, because if the value is dirty as well,
		// the store knows it and will get to updating it by itself
		if ($value->isDirty())
		{
			$this->comma();
			
			$this->sql .= $this->store->fieldNamify($value->getName());
			$this->sql .= ' = ';
		
			if ($value->isNull())
			{
				$this->sql .= 'NULL'
			}
			else
			{
				$this->sql .= intval($value->getOriginal()->getId());
			}
		}
	}
	
	public function visitTextValue(GoodMannersTextValue $value)
	{
		if ($value->isDirty())
		{
			$this->comma();
			
			$this->sql .= $this->store->fieldNamify($value->getName());
			$this->sql .= ' = ';
			$this->sql .= $this->store->parseText($value);
		}
	}
	
	public function visitIntValue(GoodMannersIntValue $value)
	{
		if ($value->isDirty())
		{
			$this->comma();
			
			$this->sql .= fieldNamify($value->getName());
			$this->sql .= ' = ';
			$this->sql .= $this->store->parseInt($value);
		}
	}
	
	public function visitFloatValue(GoodMannerwsFloatValue $value)
	{
		
		if ($value->isDirty())
		{
			$this->comma();
			
			$this->sql .= fieldNamify($value->getName());
			$this->sql .= ' = ';
			$this->sql .= $this->store->parseFloat($value);
		}
	}
}

?>