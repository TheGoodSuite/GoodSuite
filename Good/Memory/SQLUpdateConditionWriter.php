<?php

namespace Good\Memory;

require_once dirname(__FILE__) . '/PropertyVisitor.php';
require_once dirname(__FILE__) . '/../Manners/Condition.php';
require_once dirname(__FILE__) . '/ConditionProcessor.php';

class SQLUpdateConditionWriter implements PropertyVisitor,
										  ConditionProcessor
{
	private $store;
	private $comparison;
	private $condition;
	private $first;
	
	private $currentTable;
	private $currentReference;
	private $to;
	
	private $updatingTableNumber;
	private $updatingTableValue;
	private $updatingTableName;
	
	private $joining;
	private $joinedTables;
	private $phase2;
	private $rootTableName;
	
	public function __construct(SQLStore $store, $currentTable)
	{	
		$this->store = $store;
		$this->currentTable = $currentTable;
	}
	
	public function getCondition()
	{
		return $this->condition;
	}
	
	private function getJoining()
	{
		return $this->joining;
	}
	
	private function getJoinedTables()
	{
		return $this->joinedTables;
	}
	
	public function writeCondition(\Good\Manners\Condition $condition, 
								   $rootTableName,
								   $updatingTableNumber,
								   $updatingTableName)
	{
		$this->updatingTableNumber = $updatingTableNumber;
		$this->updatingTableName = $updatingTableName;
		$this->rootTableName = $rootTableName;
		$this->store->setCurrentConditionProcessor($this);
		
		$condition->process($this->store);
	}
	
	public function writeComparisonCondition(\Good\Manners\Storable $to, $comparison)
	{
		$this->condition = '';
		
		if ($this->updatingTableNumber == $this->currentTable)
		{
			$this->updatingTableValue = $to;
		}
		else
		{
			$this->phase2 = false;
			$this->writeSimpleComparisonCondition($to, $comparison);
			
			if ($this->updatingTableValue == null)
			{
				$joins = '';
				$join = $this->store->getReverseJoin($this->updatingTableNumber);
				
				while ($join->tableNumberOrigin != 0 &&
					    !\array_key_exists($join->tableNumberOrigin, $this->joinedTables))
				{
					$join = $this->store->getReverseJoin($join->tableNumberOrigin);
					
					$sql .= ' JOIN ' . $this->store->tableNamify($join->tableNameDestination) . 
																' AS t' . $join->tableNumberDestination;
					$sql .= ' ON t' . $join->tableNumberOrigin . '.' . 
												$this->store->fieldNamify($join->fieldNameOrigin);
					$sql .= ' = t' . $join->tableNumberDestination . '.id';
					
					// They need to be added to the sql in reverse as well, or else
					// we'll get unknown table names
					$joins = $sql . $joins;
				}
				
				$this->joins .= $joins;
			}
			
			$join = $this->store->getReverseJoin($this->updatingTableNumber);
			
			$sql  = $this->store->tableNamify($join->tableNameDestination) . '.id'; 
			$sql .= ' IN (SELECT t' . $join->tableNumberOrigin . '.' .
										$this->store->fieldNamify($join->fieldNameOrigin);
			$sql .= ' FROM ' . $this->store->tableNamify($this->rootTableName) . 
														' AS t' . $join->tableNumberOrigin;
					
			$sql .= $this->joins;
			$sql .= ' WHERE ' . $this->condition;
			$this->first = false;
				
			$sql .= ')';
			
			$this->condition = $sql;
		}
		
		// If the Table isn't in our $to, so we don't have to care about doing the
		// part of $it's tree after it either
		if ($this->updatingTableValue != null)
		{
			$this->tableName = $this->store->tableNamify($this->updatingTableName);
			$this->phase2 = true;
			$this->store->setCurrentPropertyVisitor($this);
			$this->comparison = $comparison;
			$this->first = true;
			$this->currentTable = $this->updatingTableNumber;
			
			$this->updatingTableValue->acceptStore($this->store);
		}
	}
	
	
	
	public function writeSimpleComparisonCondition(\Good\Manners\Storable $to, $comparison)
	{
		$this->store->setCurrentConditionProcessor($this);
		
		$this->comparison = $comparison;
		$this->first = true;
		$this->condition = '';
		$this->joining = '';
		$this->updatingTableFound = null;
		$this->joinedTables = array();
		
		$this->store->setCurrentPropertyVisitor($this);
		$to->acceptStore($this->store);
		
		if ($this->first)
		{
			$this->condition = '1 = 1';
		}
	}
	
	
	public function processEqualityCondition(\Good\Manners\Storable $to)
	{
		$this->writeComparisonCondition($to, '=');
	}
	public function processInequalityCondition(\Good\Manners\Storable $to)
	{
		$this->writeComparisonCondition($to, '<>');
	}
	public function processGreaterCondition(\Good\Manners\Storable $to)
	{
		$this->writeComparisonCondition($to, '>');
	}
	public function processGreaterOrEqualsCondition(\Good\Manners\Storable $to)
	{
		$this->writeComparisonCondition($to, '>=');
	}
	public function processLessCondition(\Good\Manners\Storable $to)
	{
		$this->writeComparisonCondition($to, '<');
	}
	public function processLessOrEqualsCondition(\Good\Manners\Storable $to)
	{
		$this->writeComparisonCondition($to, '<=');
	}
	
	public function processAndCondition(\Good\Manners\Condition $condition1, \Good\Manners\Condition $condition2)
	{
		$this->writeCondition($condition1,
							  $this->updatingTableNumber,
							  $this->updatingTableName,
							  $this->updatingTableValue);
		$sqlCondition1 = $this->getCondition();
		
		$this->writeCondition($condition1,
							  $this->updatingTableNumber,
							  $this->updatingTableName,
							  $this->updatingTableValue);
		$sqlCondition2 = $this->getCondition();
		
		$this->condition = '(' . $sqlCondition1 . ' AND ' . $sqlCondition2 . ')';
	}
	public function processOrCondition(\Good\Manners\Condition $condition1, \Good\Manners\Condition $condition2)
	{
		$this->writeCondition($condition1,
							  $this->updatingTableNumber,
							  $this->updatingTableName,
							  $this->updatingTableValue);
		$sqlCondition1 = $this->getCondition();
		
		$this->writeCondition($condition1,
							  $this->updatingTableNumber,
							  $this->updatingTableName,
							  $this->updatingTableValue);
		$sqlCondition2 = $this->getCondition();
		
		$this->condition = '(' . $sqlCondition1 . ' OR ' . $sqlCondition2 . ')';
	}
	
	public function visitReferenceProperty($name, $datatypeName, $dirty, $null, 
															\Good\Manners\Storable $value = null)
	{
		if ($dirty)
		{
			if($null)
			{
				$this->writeBracketOrAnd();
				
				$this->writeTableName();
				$this->condition .= '.' . $this->store->fieldNamify($name) . $this->comparison . ' NULL';
			}
			else if (!$value->isNew())
			{
				$this->writeBracketOrAnd();
				
				$this->writeTableName();
				$this->condition .= '.' . $this->store->fieldNamify($name) . 
											$this->comparison . ' ' . intval($value->getId());
			}
			else
			{
				$join = $this->store->getJoin($this->currentTable, $this->currentReference);
				
				if ($join == $this->updatingTableNumber)
				{
					$this->updatingTableValue = $value;
				}
				else
				{
					if ($join == -1)
					{
						$join = $this->store->createJoin($this->currentTable, $name, $this->currentReference, $datatypeName);
					}
					
					$subWriter = new SQLUpdateConditionWriter($this->store, $join);
					$subWriter->writeSimpleComparisonCondition($value, $this->comparison);
					
					$this->store->setCurrentConditionProcessor($this);
					
					if (!$this->phase2)
					{
						$this->joining .= ' JOIN ' . $this->store->tableNamify($datatypeName) . 
																				' AS t' . $join;
						$this->joining .= ' ON t' . $this->currentTable . '.' . 
																$this->store->fieldNamify($name);
						$this->joining .= ' = t' . $join . '.id';
						
						$this->joining .= $subWriter->getJoining();
						$this->writeBracketOrAnd();
						$this->condition .= $subWriter->getCondition();
						$this->joinedTables = \array_merge($this->joinedTables, $subWriter->getJoinedTables);
					}
					else
					{
						$this->writeBracketOrAnd();
						$this->condition .= ' ' . $this->tableName . '.' . 
																$this->store->fieldNamify($name);
						$this->condition .= ' IN (SELECT t' . $join . '.id';
						$this->condition .= ' FROM ' . $this->store->tableNamify($datatypeName) . 
																	' AS t' . $join;
								
						$this->condition .= $subWriter->getJoining();
						$this->condition .= ' WHERE ' . $subWriter->getCondition();
						$this->condition .= ')';
					}
				}
			}
		}
		
		$this->currentReference++;
	}
	
	public function visitTextProperty($name, $dirty, $null, $value)
	{
		if($dirty)
		{
			$this->writeBracketOrAnd();
			$this->writeTableName();
			
			$this->condition .=  '.' . $this->store->fieldNamify($name) .
										' ' . $this->comparison . ' ';
										' ' . $this->comparison . ' ';
			if ($null)
			{
				$this->condition .= ' NULL';
			}
			else
			{
				$this->condition .= ' ' . $this->store->parseText($value);
			}
			
		}
	}
	public function visitIntProperty($name, $dirty, $null, $value)
	{
		if($dirty)
		{
			$this->writeBracketOrAnd();
			$this->writeTableName();
			
			$this->condition .=  '.' . $this->store->fieldNamify($name) . ' ' . $this->comparison;
			if ($null)
			{
				$this->condition .= ' NULL';
			}
			else
			{
				$this->condition .= ' ' . $this->store->parseInt($value);
			}
		}
	}
	public function visitFloatProperty($name, $dirty, $null, $value)
	{
		if($dirty)
		{
			$this->writeBracketOrAnd();
			$this->writeTableName();
			
			$this->condition .=  '.' . $this->store->fieldNamify($name) . ' ' . $this->comparison;
			if ($null)
			{
				$this->condition .= ' NULL';
			}
			else
			{
				$this->condition .= ' ' . $this->store->parseFloat($value);
			}
		}
	}
	public function visitDatetimeProperty($name, $dirty, $null, $value)
	{
		if($dirty)
		{
			$this->writeBracketOrAnd();
			$this->writeTableName();
			
			$this->condition .=  '.' . $this->store->fieldNamify($name) . ' ' . $this->comparison;
			if ($null)
			{
				$this->condition .= ' NULL';
			}
			else
			{
				$this->condition .= ' ' . $this->store->parseDatetime($value);
			}
		}
	}
	
	private function writeBracketOrAnd()
	{
		if ($this->first)
		{
			// removed brackets change name of function?
			//$this->condition = '(';
			$this->first = false;
		}
		else
		{
			$this->condition .= ' AND ';
		}
	}
	
	private function writeTableName()
	{
		if ($this->phase2)
		{
			$this->condition .= $this->tableName;
		}
		else
		{
			$this->condition .= 't' . $this->currentTable;
		}
	}
}

?>