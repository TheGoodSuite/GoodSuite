<?php

require_once dirname(__FILE__) . '/../Manners/Report/ValueVisitor.php';

class GoodMemorySQLInserter implements GoodMannersValueVisitor
{
	private $db;
	private $store;
	
	private $sql;
	private $values;
	private $first;
	
	public function __construct(SQLStore $store, GoodMemoryDatabase $db)
	{
		$this->db = $db;
	}
	
	
	public function insert(GoodMannersReferenceValue $value)
	{
		$this->sql = 'INSERT INTO ' . $this->store->tableNamify($value->getClassName()) . ' (';
		$this->values = 'VALUES (';
		$first = true;
		
		$value->visitMembers($this);
		
		$this->sql .= ') ';
		$this->sql .= $this->values . ')';
		
		$this->db->query($this->sql);
		$value->getOriginal()->setId($this->db->getLastInsertedId());
		$value->getOriginal()->setNew(false);
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
			$values .= ', ';
		}
	}
	
	public function visitReferenceValue(GoodMannersReferenceValue $value)
	{
		$this->comma();
		
		$this->sql .= $this->store->fieldNamify($value->getName());
	
		if ($value->isNull())
		{
			$this->values .= 'NULL'
		}
		else
		{
			$original =& $value->getOriginal();
			
			if ($original->isNew())
			{
				$inserter = new GoodMemorySQLInserter($this->store, $this->db);
				$inserter->insert($value);
			}
			
			$this->values .= intval($original->getId());
		}
	}
	
	public function visitTextValue(GoodMannersTextValue $value)
	{
		$this->comma();
		
		$this->sql .= $this->store->fieldNamify($value->getName());
		
		$this->values .= $this->store->parseText($value);
	}
	
	public function visitIntValue(GoodMannersIntValue $value)
	{
		$this->comma();
		
		$this->sql .= $this->store->fieldNamify($value->getName());
		
		$this->values .= $this->store->parseInt($value);
	}
	
	public function visitFloatValue(GoodMannerwsFloatValue $value)
	{
		$this->comma();
		
		$this->sql .= $this->store->fieldNamify($value->getName());
	
		$this->values .= $this->store->parseFloat($value);
	}
}

?>