<?php

require_once dirname(__FILE__) . '/PropertyVisitor.php';

require_once dirname(__FILE__) . '/SQLConditionWriter.php';

class GoodMemorySQLSelecter implements GoodMemoryPropertyVisitor
{
	private $db;
	private $store;
	
	private $subquery;
	
	private $sql;
	private $currentTable;
	private $currentReference;
	
	public function __construct(GoodMemorySQLStore $store, GoodMemoryDatabase $db, $currentTable)
	{
		$this->db = $db;
		$this->store = $store;
		$this->currentTable = $currentTable;
	}
	
	
	public function select($datatypeName, GoodMannersCondition $condition, GoodMannersStorable $value)
	{
		$this->sql = "SELECT t0.id AS t0_id";
		
		$this->currentReference = 0;
		$this->store->setCurrentPropertyVisitor($this);
		$value->acceptStore($this->store);
		
		$this->sql .= $this->writeQueryWithoutSelect($datatypeName, $condition);
		
		$this->db->query($this->sql);
		
		return $this->db->getResult();
	}
	
	public function writeQueryWithoutSelect($datatypeName, 
											GoodMannersCondition $condition)
	{
		$sql  = " FROM " . $this->store->tableNamify($datatypeName) . " AS t0";
		
		$conditionWriter = new GoodMemorySQLConditionWriter($this->store, 0);
		$conditionWriter->writeCondition($condition);
		
		foreach ($this->store->getJoins() as $somejoins)
		{
			foreach ($somejoins as $join)
			{
				$sql .= ' JOIN ' . $this->store->tableNamify($join->tableNameDestination) . 
															' AS t' . $join->tableNumberDestination;
				$sql .= ' ON t' . $join->tableNumberOrigin . '.' . 
											$this->store->fieldNamify($join->fieldNameOrigin);
				$sql .= ' = t' . $join->tableNumberDestination . '.id';
			}
		}
		
		$sql .= ' WHERE ' . $conditionWriter->getCondition();
		
		return $sql;
	}
	
	public function visitReferenceProperty($name, $datatypeName, $dirty, $null, 
													GoodMannersStorable $value = null)
	{
		if ($dirty)
		{
			$this->sql .= ', ';
			$this->sql .= 't' . $this->currentTable . '.' . $name;
			$this->sql .= ' AS t' . $this->currentTable . '_' . $name;
		
			if (!$null)
			{
				$join = $this->store->getJoin($this->currentTable, $this->currentReference);
				
				if ($join == -1)
				{
					$join = $this->store->createJoin($this->currentTable,
													 $name, 
													 $this->currentReference, 
													 $datatypeName);
				}
						
				$this->sql .= ', ';
				$this->sql .= 't' . $join . '.id AS t' . $join . '_id';
				
				$currentTable = $this->currentTable;
				$this->currentTable = $join;
				$value->acceptStore($this->store);
				$this->currentTable = $currentTable;
			}
		}
		
		$this->currentReference++;
	}
	
	private function visitAnything($name)
	{
		$this->sql .= ', ';
		
		$this->sql .= 't' . $this->currentTable . '.' . $this->store->fieldNamify($name);
		$this->sql .= ' AS t' . $this->currentTable . '_' . $this->store->fieldNamify($name);

	}
	
	public function visitTextProperty($name, $dirty, $null, $value)
	{
		$this->visitAnything($name);
	}
	
	public function visitIntProperty($name, $dirty, $null, $value)
	{
		$this->visitAnything($name);
	}
	
	public function visitFloatProperty($name, $dirty, $null, $value)
	{
		$this->visitAnything($name);
	}
}

?>