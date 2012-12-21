<?php

require_once dirname(__FILE__) . '/PropertyVisitor.php';

class GoodMemorySQLSimpleUpdater implements GoodMemoryPropertyVisitor
{
	private $db;
	private $store;
	
	private $sql;
	private $first;
	
	public function __construct(GoodMemorySQLStore $store, GoodMemoryDatabase $db)
	{
		$this->db = $db;
		$this->store = $store;
	}
	
	
	public function update($datatypeName, GoodMannersStorable $value)
	{
		$this->sql = 'UPDATE ' . $this->store->tableNamify($datatypeName);
		$this->sql .= ' SET ';
		
		$this->first = true;
		$this->store->setCurrentPropertyVisitor($this);
		$value->acceptStore($this->store);
		
		$this->sql .= " WHERE id = " . intval($value->getId()) . "";
		
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
			$this->sql .= ', ';
		}
	}
	
	public function visitReferenceProperty($name, $datatypeName, $dirty, $null, 
															GoodMannersStorable $value = null)
	{
		// We don't need to recurse, because if the value is dirty as well,
		// the store knows it and will get to updating it by itself
		if ($dirty)
		{
			$this->comma();
			
			$this->sql .= $this->store->fieldNamify($name);
			$this->sql .= ' = ';
		
			if ($null)
			{
				$this->sql .= 'NULL';
			}
			else
			{
				$this->sql .= intval($value->getId());
			}
		}
	}
	
	public function visitTextProperty($name, $dirty, $null, $value)
	{
		if ($dirty)
		{
			$this->comma();
			
			$this->sql .= $this->store->fieldNamify($name);
			$this->sql .= ' = ';
			
			if ($null)
			{
				$this->sql .= 'NULL';
			}
			else
			{
				$this->sql .= $this->store->parseText($value);
			}
		}
	}
	
	public function visitIntProperty($name, $dirty, $null, $value)
	{
		if ($dirty)
		{
			$this->comma();
			
			$this->sql .= $this->store->fieldNamify($name);
			$this->sql .= ' = ';
			
			if ($null)
			{
				$this->sql .= 'NULL';
			}
			else
			{
				$this->sql .= $this->store->parseInt($value);
			}
		}
	}
	
	public function visitFloatProperty($name, $dirty, $null, $value)
	{
		if ($dirty)
		{
			$this->comma();
			
			$this->sql .= $this->store->fieldNamify($name);
			$this->sql .= ' = ';
			
			if ($null)
			{
				$this->sql .= 'NULL';
			}
			else
			{
				$this->sql .= $this->store->parseFloat($value);
			}
		}
	}
}

?>