<?php

require_once dirname(__FILE__) . '/../Manners/StoreSpecifications/ComparingStore.php';
require_once dirname(__FILE__) . '/../Manners/StoreSpecifications/BasicLogicStore.php';

require_once dirname(__FILE__) . '/../Manners/Resolver.php';

require_once dirname(__FILE__) . '/SQLSimpleUpdater.php';
require_once dirname(__FILE__) . '/SQLAdvancedUpdater.php';
require_once dirname(__FILE__) . '/SQLInserter.php';
require_once dirname(__FILE__) . '/SQLSelecter.php';
require_once dirname(__FILE__) . '/SQLJoin.php';
require_once dirname(__FILE__) . '/ConditionProcessor.php';

abstract class GoodMemoryBaseSQLStore extends GoodMannersStore
									  implements GoodMannersComparingStore,
												 GoodMannersBasicLogicStore
{
	protected $db;
	private $currentConditionWriter = null;
	private $currentPropertyVisitor = null;
	
	private $joins = array();
	private $joinsReverse = array();
	private $numberOfJoins = 0;
	
	private $postponed = array();
	
	public function __construct($db)
	{
		parent::__construct();
	
		$this->db = $db;
	}
	
	public function tableNamify($value)
	{
		return strtolower($value);
	}
	
	public function fieldNamify($value)
	{
		return strtolower($value);
	}
	
	public function parseInt($value)
	{
		return intval($value);
	}
	
	public function parseFloat($value)
	{
		return floatval($value);
	}
	
	public function parseDatetime($value)
	{
		// shouldn't be necessary when we do stricter type checking,
		// but let's just stick with it for now.
		if (!($value instanceof DateTime))
		{
			// TODO: turn this into real error reporting
			die("Non-DateTime given for a DateTime field.");
		}
		
		return "'" . $value->format('Y-m-d H:i:s') . "'";
	}
	
	public function parseText($value)
	{
		return "'" . $this->db->escapeText($value) . "'";
	}
	
	protected function saveAnyDeletions($datatypeName, array $storables)
	{
		foreach ($storables as $storable)
		{
			$sql  = 'UPDATE ' . $this->tableNamify($datatypeName);
			$sql .= ' SET deleted = TRUE';
			$sql .= " WHERE id = " . intval($storable->getId());
			
			$this->db->query($sql);
		}
	}
	
	protected function saveAnyNew($datatypeName, array $storables)
	{
		$inserter = new GoodMemorySQLInserter($this, $this->db);
		
		foreach ($storables as $storable)
		{
			// We check again if it is new, as it might already be inserted when resolving dependencies
			// of another insert, in which case it is not new anymore.
			if ($storable->isNew())
			{
				$inserter->insert($datatypeName, $storable);
			}
		}
		
		foreach ($inserter->getPostponed() as $postponed)
		{
			$postponed->doNow();
		}
		
		$this->reflush();
	}
	
	protected function saveAnyModifications($datatypeName, array $storables)
	{
		$updater = new GoodMemorySQLSimpleUpdater($this, $this->db);
		
		foreach ($storables as $storable)
		{
			$updater->update($datatypeName, $storable);
			$storable->makeDirty(false);
		}
	}
	
	protected function doAnyGet($datatypeName, 
								GoodMannersCondition $condition, 
								GoodMannersResolver $resolver)
	{
		$this->joins = array(0 => array());
		$this->numberOfJoins = 0;
		
		$selecter = new GoodMemorySQLSelecter($this, $this->db, 0);
		
		return $selecter->select($datatypeName, $condition, $resolver);
	}
	
	protected function doAnyModify($datatypeName,
								GoodMannersCondition $condition,
								GoodMannersStorable $modifications)
	{
		$this->joins = array(0 => array());
		$this->numberOfJoins = 0;
		
		$updater = new GoodMemorySQLAdvancedUpdater($this, $this->db, 0);
		
		$updater->update($datatypeName, $condition, $modifications);
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
	
	public function getReverseJoin($tableNumber)
	{
		if (array_key_exists($tableNumber, $this->joinsReverse))
		{
			return $this->joinsReverse[$tableNumber];
		}
		else
		{
			return null;
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
		
		$join = new GoodMemorySQLJoin($tableNumberOrigin,
									  $this->fieldNamify($fieldNameOrigin),
									  $this->tableNamify($tableNameDestination),
									  $this->numberOfJoins);
		
		$this->joins[$tableNumberOrigin][$fieldNumberOrigin] = $join;
		
		$this->joins[$this->numberOfJoins] = array();
		$this->joinsReverse[$this->numberOfJoins] = $join;
		
		return $this->numberOfJoins;
	}
	
	public function createEqualityCondition(GoodMannersStorable $to)
	{
		return new GoodMannersEqualityCondition($this, $to);
	}
	public function createInequalityCondition(GoodMannersStorable $to)
	{
		return new GoodMannersInequalityCondition($this, $to);
	}
	public function createGreaterCondition(GoodMannersStorable $to)
	{
		return new GoodMannersGreaterCondition($this, $to);
	}
	public function createGreaterOrEqualsCondition(GoodMannersStorable $to)
	{
		return new GoodMannersGreaterOrEqualsCondition($this, $to);
	}
	public function createLessCondition(GoodMannersStorable $to)
	{
		return new GoodMannersLessCondition($this, $to);
	}
	public function createLessOrEqualsCondition(GoodMannersStorable $to)
	{
		return new GoodMannersLessOrEqualsCondition($this, $to);
	}
	public function createAndCondition(GoodMannersCondition $condition1, GoodMannersCondition $condition2)
	{
		return new GoodMannersAndCondition($this, $condition1, $condition2);
	}
	public function createOrCondition(GoodMannersCondition $condition1, GoodMannersCondition $condition2)
	{
		return new GoodMannersOrCondition($this, $condition1, $condition2);
	}
	
	public function setCurrentConditionProcessor(GoodMemoryConditionProcessor $value)
	{
		$this->currentConditionWriter = $value;
	}
	
	public function processEqualityCondition(GoodMannersStorable $to)
	{
		$this->currentConditionWriter->processEqualityCondition($to);
	}
	public function processInequalityCondition(GoodMannersStorable $to)
	{
		$this->currentConditionWriter->processInequalityCondition($to);
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
	
	public function setCurrentPropertyVisitor(GoodMemoryPropertyVisitor $value)
	{
		$this->currentPropertyVisitor = $value;
	}
	
	public function visitReferenceProperty($name, $datatypeName, $dirty, $null, 
													GoodMannersStorable $value = null)
	{
		$this->currentPropertyVisitor->visitReferenceProperty($name, $datatypeName, $dirty, $null, $value);
	}
	public function visitTextProperty($name, $dirty, $null, $value)
	{
		$this->currentPropertyVisitor->visitTextProperty($name, $dirty, $null, $value);
	}
	public function visitIntProperty($name, $dirty, $null, $value)
	{
		$this->currentPropertyVisitor->visitIntProperty($name,$dirty,  $null, $value);
	}
	public function visitFloatProperty($name, $dirty, $null, $value)
	{
		$this->currentPropertyVisitor->visitFloatProperty($name, $dirty, $null, $value);
	}
	public function visitDatetimeProperty($name, $dirty, $null, $value)
	{
		$this->currentPropertyVisitor->visitDatetimeProperty($name, $dirty, $null, $value);
	}
}

?>