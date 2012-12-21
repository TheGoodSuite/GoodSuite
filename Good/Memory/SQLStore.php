<?php

require_once dirname(__FILE__) . '/../Manners/Store.php';
require_once dirname(__FILE__) . '/../Manners/StoreSpecifications/ComparingStore.php';
require_once dirname(__FILE__) . '/../Manners/StoreSpecifications/BasicLogicStore.php';
require_once 'SQLInserter.php';

class GoodMemorySQLStore extends GoodMannersStore 
					     implements GoodMannersComparingStore, 
									GoodMannersBasicLogicStore
{
	private $db;
	private $joins;
	private $numberOfJoins;
	
	public function __construct(GoodMemoryDatabase $db)
	{
		$this->db = $db;
		$this->joins = array();
		$this->numberOfJoins = 0;
	}
	
	public function getJoin($table, $property)
	{
		if (array_key_exists($property, $this->joins[$table]))
		{
			return $this->joins[$table][$property]->tableNumberDestination;
		}
		else
		{
			return -1;
		}
	}
	
	public function getJoins()
	{
		return $this->joins;
	}
	
	public function createJoin($tableNumberOrigin, $fieldNameOrigin, $fieldNumberOrigin, $tableNameDestination)
	{
		// we start off with increment because joins index is numberOfJoins + 1 (index 0 is for base table)
		$this->numberOfJoins++;
		
		$this->joins[$tableNumberOrigin][$fieldNumberOrigin] = new GoodMemorySQLJoin($tableNumberOrigin,
																					 $this->fieldNamify($fieldNameOrigin),
																					 $this->tableNamify($tableNameDestination),
																					 $this->numberOfJoins);
		
		$this->joins[$this->numberOfJoins] = array();
		
		return $this->numberOfJoins;
	}
	
	protected function saveNew(array &$entries)
	{
		$inserter = new GoodMemorySQLInsterter($this, $this->db);
		
		foreach ($entries as &$entry)
		{
			// We check again if it is new, as it might already be inserted when resolving dependencies of another
			// insert, in which case it is not new anymore.
			if ($entry->isNew())
			{
				$inserter->insert($entry->report());
			}
		}
	}
	
	protected function saveModifications(array &$entries)
	{
		$updater = new GoodMemorySQLSimpleUpdater($this, $this->db);
		
		foreach ($entries as &$entry)
		{
			$updater->update($entry->report());
		}
	}
	
	protected function saveDeletions(array &$entries)
	{
		$deleter = new GoodMemorySQLDeleter($this, $this->db);
		
		foreach ($entries as &$entry)
		{
			$deleter->delete($entry->report());
		}
	}
	
	public function tableNamify($value)
	{
		return strtolower($value);
	}
	
	public function fieldNamify($value)
	{
		return strtolower($value);
	}
	
	protected function doGet(GoodMannersCondition $condition, GoodMannersStorable $resolver)
	{
		$this->joins = array(0 => array());
		$this->numberOfJoins = 0;
		
		$selecter = new GoodMemorySQLSelecter($this, $this->db, 0);
		
		return $selecter->select($condition, $resolver->report());
	}
	
	protected function doModify(GoodMannersCondition $condition, GoodMannersStorable &$modifications)
	{
		$this->joins = array(0 => array());
		$this->numberOfJoins = 0;
		
		$updater = new GoodMemorySQLAdvancedUpdater($this, $this->db, 0);
		
		$updater->update($condition, $modifications->report());
	}
	
	private $currentConditionWriter;
	
	public function setCurrentConditionWriter(GoodMemoryConditionWriter $value)
	{
		$this->currentConditionWriter = $value;
	}
	
	public function parseInt($value)
	{
		if ($value->isNull())
		{
			return 'NULL';
		}
		else
		{
			return intval($value->getValue());
		}
	}
	
	public function parseFloat($value)
	{
		if ($value->isNull())
		{
			return 'NULL';
		}
		else
		{
			return floatval($value->getValue());
		}
	}
	
	public function parseText()
	{
		if ($value->isNull())
		{
			return 'NULL'
		}
		else
		{
			return "'" . $this->db->escapeText($value->getValue()) . "'";
		}
	}
	
	public function processEqualityCondition(GoodMannersStorable $to)
	{
		$this->currentConditionWriter->processEqualityCondition($to);
	}
	public function processInequalityCondition(GoodMannersStorable $to)
	{
		$this->currentConditionWriter->processEqualityCondition($to);
	}
	public function processGreaterCondition(GoodMannersStorable $to)
	{
		$this->currentConditionWriter->processGreaterCondition($to);
	}
	public function processGreaterOrEqualsCondition(GoodMannersStorable $to)
	{
		$this->currentConditionWriter->processGreaterOrEqualsCondition($to);
	}
	public function processLessCondition(GoodMannersStorable $to)
	{
		$this->currentConditionWriter->processLessCondition($to);
	}
	public function processLessOrEqualsCondition(GoodMannersStorable $to)
	{
		$this->currentConditionWriter->processLessOrEqualsCondition($to);
	}
	public function processAndCondition(GoodMannersCondition $condition1, GoodMannersCondition $condition2)
	{
		$this->currentConditionWriter->processAndCondition($condition1, $condition2);
	}
	public function processOrCondition(GoodMannersCondition $condition1, GoodMannersCondition $condition2)
	{
		$this->currentConditionWriter->processOrCondition($condition1, $condition2);
	}
	
	public function createEqualsCondition(GoodMannersStorable $to);
	public function createGreaterCondition(GoodMannersStorable $to);
	public function createGreaterOrEqualsCondition(GoodMannersStorable $to);
	public function createLessCondition(GoodMannersStorable $to);
	public function createLessOrEqualsCondition(GoodMannersStorable $to);
	public function createAndCondition(GoodMannersCondition $condition1, GoodMannersCondition $condition2);
	public function createOrCondition(GoodMannersCondition $condition1, GoodMannersCondition $condition2);
}

?>
	
	public function 
}