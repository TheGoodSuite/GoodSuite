<?php

require_once dirname(__FILE__) . '/../Manners/Report/ValueVisitor.php';

class GoodMemorySQLSelecter implements GoodMannersValueVisitor
{
	private $db;
	private $store;
	
	private $subquery;
	
	private $sql;
	private $currentTable;
	private $currentReference;
	
	public function __construct(SQLStore $store, GoodMemoryDatabase $db, $currentTable)
	{
		$this->db = $db;
		$this->store = $store;
		$this->currentTable = $currentTable;
	}
	
	
	public function select(GoodMannersCondition $condition, GoodMannersReferenceValue $value)
	{
		$this->sql = "SELECT t0.id";
		
		$this->currentReference = 0;
		$value->visitMembers($this);
		
		$this->sql .= " FROM " . $this->store->tableNamify($value->getClassName()) " AS t0";
		
		$conditionWriter = new GoodMemorySQLConditionWriter($this->store, 0);
		$conditionWriter->writeCondition($condition);
		
		$classMap = array();
		$classMap[0] = $value->getClassName()
		
		foreach ($this->store->getJoins() as $somejoins)
		{
			foreach ($somejoins as $join)
			{
				$this->sql .= ' JOIN ' . $this->store->tableNamify($join->tableNameDestination . ' AS t' . $join->tableNumberDestination;
				$this->sql .= ' ON t' . $join->tableNumberOrigin . '.' . $this->store->fieldNamify($join->fieldNameOrigin);
				$this->sql .= ' = t' . $join->tableNumberDestination . '.id';
				
				$classMap[$join->tableNumberDestination] = $join->tableNameDestination;
			}
		}
		
		$this->sql .= ' WHERE ' . $conditionWriter->getCondition();
		
		$this->db->query($this->sql);
		
		// TODO: parse results
	}
	
	public function visitReferenceValue(GoodMannersReferenceValue $value)
	{
		if ($value->isDirty())
		{
			if (!$value->isNull() && $value->getOriginal()->isBlank())
			{
				$join = $this->store->getJoin($this->currentTable, $this->currentReference);
						
				$this->sql .= 't' . $join . '.id AS t' . $join . '_id';
				
				$currentTable = $this->currentTable;
				$this->currentTable = $join;
				$value->visitMembers($this);
				$this->currentTable = $currentTable;
			}
			else
			{
				$sql .= ', ';
				
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
		$sql .= ', ';
		
		$this->sql .= 't' . $this->currentTable . '.' $this->store->fieldNamify($value->getName());
		$this->sql .= ' AS t' . $this->currentTable . '_' . $this->store->fieldNamify($value->getName());
	}
	
	public function visitIntValue(GoodMannersIntValue $value)
	{
		$sql .= ', ';
		
		$this->sql .= 't' . $this->currentTable . '.' $this->store->fieldNamify($value->getName());
		$this->sql .= ' AS t' . $this->currentTable . '_' . $this->store->fieldNamify($value->getName());
	}
	
	public function visitFloatValue(GoodMannerwsFloatValue $value)
	{
		$sql .= ', ';
		
		$this->sql .= 't' . $this->currentTable . '.' $this->store->fieldNamify($value->getName());
		$this->sql .= ' AS t' . $this->currentTable . '_' . $this->store->fieldNamify($value->getName());
	}
}

?>