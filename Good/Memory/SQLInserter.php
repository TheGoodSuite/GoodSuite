<?php

namespace Good\Memory;

use Good\Manners\Storable;

class SQLInserter implements PropertyVisitor
{
	private $db;
	private $store;
	
	private $sql;
	private $values;
	private $first;
	
	private $inserting;
	private $postponed;
	
	public function __construct(SQLStore $store, Database\Database $db)
	{
		$this->db = $db;
		$this->store = $store;
		$this->postponed = array();
	}
	
	
	public function insert($datatypeName, Storable $value)
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
														Storable $value = null)
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
					$inserter = new SQLInserter($this->store, $this->db);
					$inserter->insert($datatypeName, $value);
					$this->postponed = \array_merge($this->postponed, $inserter->getPostponed());
					$this->store->setCurrentPropertyVisitor($this);
				}
				
				if ($value->isNew() && $value->getId() == -1)
				// $value is actually new, but not marked as such to prevent infinite recursion
				{
					$this->postponed[] = new SQLPostponedForeignKey($this->inserting,
																	$name,
																	$value);
				}
				else
				{
					$this->values .= \intval($value->getId());
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
	
	public function visitDatetimeProperty($name, $dirty, $null, $value)
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
				$this->values .= $this->store->parseDatetime($value);
			}
		}
	}
}

?>