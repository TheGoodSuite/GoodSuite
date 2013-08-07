<?php

namespace Good\Memory\SQL;

use Good\Memory\SQLStore;
use Good\Memory\PropertyVisitor;
use Good\Manners\Storable;
use Good\Manners\Condition;
use Good\Manners\ConditionProcessor;

$started = false;

class UpdateConditionWriter implements PropertyVisitor,
									   ConditionProcessor
{
	private $store;
	private $comparison;
	private $condition;
	private $first;
	
	private $currentTable;
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
	
	public function writeCondition(Condition $condition, 
								   $rootTableName,
								   $updatingTableNumber,
								   $updatingTableName)
	{
		$this->updatingTableNumber = $updatingTableNumber;
		$this->updatingTableName = $updatingTableName;
		$this->rootTableName = $rootTableName;
		
		$this->first = true;
		
		$condition->process($this);
	}
	
	public function writeComparisonCondition(Storable $to, $comparison)
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
			
			$joins = '';
			
			if ($this->updatingTableValue == null)
			{
				$join = $this->store->getReverseJoin($this->updatingTableNumber);
				
				while ($join->tableNumberOrigin != 0)
				{
					$join = $this->store->getReverseJoin($join->tableNumberOrigin);
					
					$sql = ' JOIN ' . $this->store->tableNamify($join->tableNameDestination) . 
																' AS t' . $join->tableNumberDestination;
					$sql .= ' ON t' . $join->tableNumberOrigin . '.' . 
												$this->store->fieldNamify($join->fieldNameOrigin);
					$sql .= ' = t' . $join->tableNumberDestination . '.id';
					
					// They need to be added to the sql in reverse as well, or else
					// we'll get unknown table names
					$joins = $sql . $joins;
				}
			}
			
			$join = $this->store->getReverseJoin($this->updatingTableNumber);
			
			$sql  = $this->store->tableNamify($join->tableNameDestination) . '.id'; 
			$sql .= ' IN (SELECT t' . $join->tableNumberOrigin . '.' .
										$this->store->fieldNamify($join->fieldNameOrigin);
			$sql .= ' FROM ' . $this->store->tableNamify($this->rootTableName) . 
														' AS t0';
					
			$sql .= $joins;
			$sql .= ' WHERE ' . $this->condition;
				
			$sql .= ')';
			$this->writeBracketOrAnd();
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
			$this->writeBracketOrAnd();
			$this->first = true;
			$this->currentTable = $this->updatingTableNumber;
			
			$this->updatingTableValue->acceptStorableVisitor($this->store);
		}
		
		if ($this->first)
		{
			$this->condition = '1 = 1';
		}
	}
	
	
	
	public function writeSimpleComparisonCondition(Storable $to, $comparison)
	{
		$this->comparison = $comparison;
		$this->first = true;
		$this->condition = '';
		$this->joining = '';
		$this->updatingTableFound = null;
		
		$this->store->setCurrentPropertyVisitor($this);
		$to->acceptStorableVisitor($this->store);
		
		if ($this->first)
		{
			$this->condition = '1 = 1';
		}
	}
	
	
	public function processEqualityCondition(Storable $to)
	{
		$this->writeComparisonCondition($to, '=');
	}
	public function processInequalityCondition(Storable $to)
	{
		$this->writeComparisonCondition($to, '<>');
	}
	public function processGreaterCondition(Storable $to)
	{
		$this->writeComparisonCondition($to, '>');
	}
	public function processGreaterOrEqualsCondition(Storable $to)
	{
		$this->writeComparisonCondition($to, '>=');
	}
	public function processLessCondition(Storable $to)
	{
		$this->writeComparisonCondition($to, '<');
	}
	public function processLessOrEqualsCondition(Storable $to)
	{
		$this->writeComparisonCondition($to, '<=');
	}
	
	public function processAndCondition(Condition $condition1, Condition $condition2)
	{
		$this->writeCondition($condition1,
							  $this->rootTableName,
							  $this->updatingTableNumber,
							  $this->updatingTableName);
		$sqlCondition1 = $this->getCondition();
		
		$this->writeCondition($condition2,
							  $this->rootTableName,
							  $this->updatingTableNumber,
							  $this->updatingTableName);
		$sqlCondition2 = $this->getCondition();
		
		$this->condition = '(' . $sqlCondition1 . ' AND ' . $sqlCondition2 . ')';
	}
	public function processOrCondition(Condition $condition1, Condition $condition2)
	{
		$this->writeCondition($condition1,
							  $this->rootTableName,
							  $this->updatingTableNumber,
							  $this->updatingTableName);
		$sqlCondition1 = $this->getCondition();
		
		$this->writeCondition($condition2,
							  $this->rootTableName,
							  $this->updatingTableNumber,
							  $this->updatingTableName);
		$sqlCondition2 = $this->getCondition();
		
		$this->condition = '(' . $sqlCondition1 . ' OR ' . $sqlCondition2 . ')';
	}
	
	public function visitReferenceProperty($name, $datatypeName, $dirty, 
															Storable $value = null)
	{
		if ($dirty)
		{
			if($value === null)
			{
				$this->writeBracketOrAnd();
				
				$this->writeTableName();
				
				if ($this->comparison == '=')
				{
					$this->condition .= '.' . $this->store->fieldNamify($name) . ' IS NULL';
				}
				else // if ($this->comparison == '<>')
				{
					$this->condition .= '.' . $this->store->fieldNamify($name) . ' IS NOT NULL';
				}
				
				// todo: error out if another comparison
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
				$join = $this->store->getJoin($this->currentTable, $name);
				
				if ($join == $this->updatingTableNumber)
				{
					$this->updatingTableValue = $value;
				}
				else
				{
					if ($join == -1)
					{
						$join = $this->store->createJoin($this->currentTable, $name, $datatypeName);
					}
					
					$subWriter = new UpdateConditionWriter($this->store, $join);
					$subWriter->writeSimpleComparisonCondition($value, $this->comparison);
					
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
	}
	
	public function visitTextProperty($name, $dirty, $value)
	{
		if($dirty)
		{
			$this->writeBracketOrAnd();
			$this->writeTableName();
			
			$this->condition .=  '.' . $this->store->fieldNamify($name) . ' ';
			
			if ($value === null)
			{
				if ($this->comparison == '=')
				{
					$this->condition .= 'IS NULL';
				}
				else // if ($this->comparison == '<>')
				{
					$this->condition .= 'IS NOT NULL';
				}
				
				// todo: error out if another comparison
			}
			else
			{
				$this->condition .=  $this->comparison . ' ' . $this->store->parseText($value);
			}
			
		}
	}
	public function visitIntProperty($name, $dirty, $value)
	{
		if($dirty)
		{
			$this->writeBracketOrAnd();
			$this->writeTableName();
			
			$this->condition .=  '.' . $this->store->fieldNamify($name) . ' ';
			
			if ($value === null)
			{
				if ($this->comparison == '=')
				{
					$this->condition .= 'IS NULL';
				}
				else // if ($this->comparison == '<>')
				{
					$this->condition .= 'IS NOT NULL';
				}
				
				// todo: error out if another comparison
			}
			else
			{
				$this->condition .= $this->comparison .' ' . $this->store->parseInt($value);
			}
		}
	}
	public function visitFloatProperty($name, $dirty, $value)
	{
		if($dirty)
		{
			$this->writeBracketOrAnd();
			$this->writeTableName();
			
			$this->condition .=  '.' . $this->store->fieldNamify($name) . ' ';
			
			if ($value === null)
			{
				if ($this->comparison == '=')
				{
					$this->condition .= 'IS NULL';
				}
				else // if ($this->comparison == '<>')
				{
					$this->condition .= 'IS NOT NULL';
				}
				
				// todo: error out if another comparison
			}
			else
			{
				$this->condition .= $this->comparison . ' ' . $this->store->parseFloat($value);
			}
		}
	}
	public function visitDatetimeProperty($name, $dirty, $value)
	{
		if($dirty)
		{
			$this->writeBracketOrAnd();
			$this->writeTableName();
			
			$this->condition .=  '.' . $this->store->fieldNamify($name) . ' ';
			if ($value === null)
			{
				if ($this->comparison == '=')
				{
					$this->condition .= 'IS NULL';
				}
				else // if ($this->comparison == '<>')
				{
					$this->condition .= 'IS NOT NULL';
				}
				
				// todo: error out if another comparison
			}
			else
			{
				$this->condition .= $this->comparison . ' ' . $this->store->parseDatetime($value);
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