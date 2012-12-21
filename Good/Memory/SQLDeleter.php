<?php

require_once dirname(__FILE__) . '/../Manners/Report/ValueVisitor.php';

class GoodMemorySQLDeleter
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
	
	
	public function update(GoodMannersReferenceValue $value)
	{
		$this->sql = 'UPDATE ' . $this->store->tableNamify($value->getClassName());
		$this->sql .= ' SET deleted = TRUE';
		$this->sql .= " WHERE id = " . intval($value->getOriginal()->getId());
		
		$this->db->query($this->sql);
	}
}

?>