<?php

require_once dirname(__FILE__) . '/PropertyVisitor.php';
require_once dirname(__FILE__) . '/SQLPostponedForeignKey.php';

class GoodMemorySQLInserter implements GoodMemoryPropertyVisitor
{
	private $db;
	private $store;
	
	private $sql;
	private $values;
	private $first;
	
	private $inserting;
	private $postponed;
	
	public function __construct(GoodMemorySQLStore $store, GoodMemoryDatabase $db)
	{
		$this->db = $db;
		$this->store = $store;
		$this->postponed = array();
	}
	
	
	public function insert($datatypeName, GoodMannersStorable $value)
	{
		$this->sql = 'INSERT INTO ' . $this->store->tableNamify($datatypeName) . ' (';
		$this->values = 'VALUES (';
		$this->first = true;
		
		$this->inserting = $value;
		
		$value->setNew(false);
		$value->setStore($this->store);
		
		$this->store->setCurrentPropertyVisitor($this);
		$value->acceptStore($this->store);
		
		$this->sql .= ') ';
		$this->sql .= $this->values . ')';
		
		$this->db->query($this->sql);
		$value->setId($this->db->getLastInsertedId());
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
			$this->values .= ', ';
		}
	}
	
	public function getPostponed()
	{
		return $this->postponed;
	}
	
	public function visitReferenceProperty($name, $datatypeName, $dirty, $null, 
														GoodMannersStorable $value = null)
	{
		// If not dirty, do not include field and use default value
		if ($dirty)
		{
			$this->comma();
			
			$this->sql .= $this->store->fieldNamify($name);
		
			if ($null)
			{
				$this->values .= 'NULL';
			}
			else
			{
				if ($value->isNew())
				{
					$inserter = new GoodMemorySQLInserter($this->store, $this->db);
					$inserter->insert($datatypeName, $value);
					$this->postponed = array_merge($this->postponed, $inserter->getPostponed());
					$this->store->setCurrentPropertyVisitor($this);
				}
				
				if ($value->isNew() && $value->getId() == -1)
				// $value is actually new, but not marked as such to prevent infinite recursion
				{
					$this->postponed[] = new GoodMemorySQLPostponedForeignKey($this->inserting,
																			  $name,
																			  $value);
				}
				else
				{
					$this->values .= intval($value->getId());
				}
			}
		}
	}
	
	public function visitTextProperty($name, $dirty, $null, $value)
	{
		// If not dirty, do not include field and use default value
		if ($dirty)
		{
			$this->comma();
			
			$this->sql .= $this->store->fieldNamify($name);
			
			if ($null)
			{
				$this->sql .= 'NULL';
			}
			else
			{
				$this->values .= $this->store->parseText($value);
			}
		}
	}
	
	public function visitIntProperty($name, $dirty, $null, $value)
	{
		// If not dirty, do not include field and use default value
		if ($dirty)
		{
			$this->comma();
			
			$this->sql .= $this->store->fieldNamify($name);
			
			if ($null)
			{
				$this->sql .= 'NULL';
			}
			else
			{
				$this->values .= $this->store->parseInt($value);
			}
		}
	}
	
	public function visitFloatProperty($name, $dirty, $null, $value)
	{
		// If not dirty, do not include field and use default value
		if ($dirty)
		{
			$this->comma();
			
			$this->sql .= $this->store->fieldNamify($name);
		
			if ($null)
			{
				$this->sql .= 'NULL';
			}
			else
			{
				$this->values .= $this->store->parseFloat($value);
			}
		}
	}
}

?>