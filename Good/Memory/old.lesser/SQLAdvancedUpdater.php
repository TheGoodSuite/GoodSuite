<?php

require_once dirname(__FILE__) . '/../Manners/Report/ValueVisitor.php';

class GoodMemorySQLAdvancedUpdater implements GoodMannersValueVisitor
{
	private $db;
	private $store;
	
	private $subquery;
	
	private $sql;
	private $first;
	private $currentTable;
	private $currentReference;
	
	public function __construct(SQLStore $store, GoodMemoryDatabase $db, $currentTable)
	{
		$this->db = $db;
		$this->store = $store;
		$this->currentTable = $currentTable;
	}
	
	
	public function update(GoodMannersCondition $condition, GoodMannersReferenceValue $value)
	{
		$joinDiscoverer = new GoodMemorySQLJoinDiscoverer($this->store, 0);
		$joinDiscoverer->discoverJoins($value);
		
		$selecter = new GoodMemorySQLSelecter($this->store, $this->db);
		$subquery = $selecter->writeQueryWithoutSelect($condition);
		
		$this->updateWithSubquery($subquery, $value);
	}
	
	public function updateWithSubquery($subquery, GoodMannersReferenceValue $value)
	{
		$this->subquery = $subquery;
		
		$this->sql = 'UPDATE ' . $this->store->tableNamify($value->getClassName());
		$this->sql .= ' SET ';
		
		$this->first = true;
		$this->currentReference = 0;
		$value->visitMembers($this);
		
		// if we haven't a single entry to update, we don't do anything
		// (there is no reason for alarm, though, it may just be that this
		//  table is only used in the WHERE or ON clause)
		if (!$this->first)
		{
			$this->sql .= ' WHERE id IN (SELECT t' . $this->currentTable  . '.id '. $this->subquery . ')';
			
			$this->db->query($this->sql);
		}
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
		if ($value->isDirty())
		{
			if (!$value->isNull() && $value->getOriginal()->isBlank())
			{
				$join = $this->store->getJoin($this->currentTable, $this->currentReference);
				
				$updater = new GoodMemorySQLAdvancedUpdater($this->store, $this->db, $join);
				$updater->updateWithSubquery($this->subquery, $value);
			}
			else
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
		
		$currentReference++;
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