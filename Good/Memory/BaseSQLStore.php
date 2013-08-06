<?php

namespace Good\Memory;

use Good\Manners\Storable;
use Good\Manners\Condition;
use Good\Manners\Resolver;

abstract class BaseSQLStore extends \GoodMannersStore // (generated so not namespaced)
							implements SQLStore
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
		return \strtolower($value);
	}
	
	public function fieldNamify($value)
	{
		return \strtolower($value);
	}
	
	public function parseInt($value)
	{
		return \intval($value);
	}
	
	public function parseFloat($value)
	{
		return \floatval($value);
	}
	
	public function parseDatetime($value)
	{
		// shouldn't be necessary when we do stricter type checking,
		// but let's just stick with it for now.
		if (!($value instanceof \DateTime))
		{
			// TODO: turn this into real error reporting
			throw new \Exception("Non-DateTime given for a DateTime field.");
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
			$sql  = 'DELETE FROM ' . $this->tableNamify($datatypeName);
			$sql .= " WHERE id = " . intval($storable->getId());
			
			$this->db->query($sql);
		}
	}
	
	protected function saveAnyNew($datatypeName, array $storables)
	{
		$inserter = new SQL\Inserter($this, $this->db);
		
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
		$updater = new SQL\SimpleUpdater($this, $this->db);
		
		foreach ($storables as $storable)
		{
			$updater->update($datatypeName, $storable);
			$storable->makeDirty(false);
		}
	}
	
	protected function doAnyGet($datatypeName, 
								Condition $condition, 
								Resolver $resolver)
	{
		$this->joins = array(0 => array());
		$this->numberOfJoins = 0;
		
		$selecter = new SQL\Selecter($this, $this->db, 0);
		
		return $selecter->select($datatypeName, $condition, $resolver);
	}
	
	protected function doAnyModify($datatypeName,
								   Condition $condition,
								   Storable $modifications)
	{
		$this->joins = array(0 => array());
		$this->numberOfJoins = 0;
		
		$updater = new SQL\AdvancedUpdater($this, $this->db, 0);
		
		$updater->update($datatypeName, $condition, $modifications);
	}
	
	public function getJoin($table, $field)
	{
		if (\array_key_exists($field, $this->joins[$table]))
		{
			return $this->joins[$table][$field]->tableNumberDestination;
		}
		else
		{
			return -1;
		}
	}
	
	public function getReverseJoin($tableNumber)
	{
		if (\array_key_exists($tableNumber, $this->joinsReverse))
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
	
	public function createJoin($tableNumberOrigin, $fieldNameOrigin, $tableNameDestination)
	{
		// we start off with increment because joins index is numberOfJoins + 1 (index 0 is for base table)
		$this->numberOfJoins++;
		
		$join = new SQL\Join($tableNumberOrigin,
							 $fieldNameOrigin,
							 $tableNameDestination,
							 $this->numberOfJoins);
		
		$this->joins[$tableNumberOrigin][$fieldNameOrigin] = $join;
		
		$this->joins[$this->numberOfJoins] = array();
		$this->joinsReverse[$this->numberOfJoins] = $join;
		
		return $this->numberOfJoins;
	}
	
	public function setCurrentPropertyVisitor(PropertyVisitor $value)
	{
		$this->currentPropertyVisitor = $value;
	}
	
	public function visitReferenceProperty($name, $datatypeName, $dirty, 
													\Good\Manners\Storable $value = null)
	{
		$this->currentPropertyVisitor->visitReferenceProperty($name, $datatypeName, $dirty, $value);
	}
	public function visitTextProperty($name, $dirty, $value)
	{
		$this->currentPropertyVisitor->visitTextProperty($name, $dirty, $value);
	}
	public function visitIntProperty($name, $dirty, $value)
	{
		$this->currentPropertyVisitor->visitIntProperty($name,$dirty, $value);
	}
	public function visitFloatProperty($name, $dirty, $value)
	{
		$this->currentPropertyVisitor->visitFloatProperty($name, $dirty, $value);
	}
	public function visitDatetimeProperty($name, $dirty, $value)
	{
		$this->currentPropertyVisitor->visitDatetimeProperty($name, $dirty, $value);
	}
}

?>