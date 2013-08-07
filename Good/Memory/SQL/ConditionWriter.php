<?php

namespace Good\Memory\SQL;

use Good\Memory\SQLStore;
use Good\Memory\PropertyVisitor;
use Good\Manners\Storable;
use Good\Manners\Condition;
use Good\Manners\ConditionProcessor;

class ConditionWriter implements PropertyVisitor,
								 ConditionProcessor
{
	private $store;
	private $comparison;
	private $condition;
	private $first;
	
	private $currentTable;
	
	public function __construct(SQLStore $store, $currentTable)
	{	
		$this->store = $store;
		$this->currentTable = $currentTable;
	}
	
	public function getCondition()
	{
		return $this->condition;
	}
	
	public function writeCondition(Condition $condition)
	{
		$condition->process($this);
	}
	
	public function writeComparisonCondition(Storable $to, $comparison)
	{
		$this->comparison = $comparison;
		$this->first = true;
		$this->condition = '';
		
		$this->store->setCurrentPropertyVisitor($this);
		$to->acceptStorableVisitor($this->store);
		
		if ($this->first)
		{
			if ($to->getId() != -1)
			{
				$this->condition .= 't' . $this->currentTable . '.id' .
										' ' . $this->comparison . ' ' . \intval($to->getId());
			}
			else
			{
				$this->condition = '1 = 1';
			}
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
		$this->writeCondition($condition1);
		$sqlCondition1 = $this->getCondition();
		
		$this->writeCondition($condition2);
		$sqlCondition2 = $this->getCondition();
		
		$this->condition = '(' . $sqlCondition1 . ' AND ' . $sqlCondition2 . ')';
	}
	public function processOrCondition(Condition $condition1, Condition $condition2)
	{
		$this->writeCondition($condition1);
		$sqlCondition1 = $this->getCondition();
		
		$this->writeCondition($condition2);
		$sqlCondition2 = $this->getCondition();
		
		$this->condition = '(' . $sqlCondition1 . ' OR ' . $sqlCondition2 . ')';
	}
	
	public function visitReferenceProperty($name, $datatypeName, $dirty, 
															Storable $value = null)
	{
		if ($dirty)
		{
			$this->writeBracketOrAnd();
			
			if($value === null)
			{
				if ($this->comparison == '=')
				{
					$this->condition .= 't' . $this->currentTable . '.' . $this->store->fieldNamify($name) . 
												' IS NULL';
				}
				else // if ($this->comparison == '<>')
				{
					$this->condition .= 't' . $this->currentTable . '.' . $this->store->fieldNamify($name) . 
												' IS NOT NULL';
				}
				
				// todo: error out if not equality or inequality
			}
			else if (!$value->isNew())
			{
				$this->condition .= 't' . $this->currentTable . '.' . $this->store->fieldNamify($name) . 
											$this->comparison . ' ' . \intval($value->getId());
				
				// todo: error out if not equality or inequality
			}
			else
			{
				$join = $this->store->getJoin($this->currentTable, $name);
				
				if ($join == -1)
				{
					$join = $this->store->createJoin($this->currentTable, $name, $datatypeName);
				}
				
				$subWriter = new ConditionWriter($this->store, $join);
				$subWriter->writeComparisonCondition($value, $this->comparison);
				
				$this->condition .= $subWriter->getCondition();
			}
		}
	}
	
	public function visitTextProperty($name, $dirty, $value)
	{
		if($dirty)
		{
			$this->writeBracketOrAnd();
		
			$this->condition .= 't' . $this->currentTable . '.' . $this->store->fieldNamify($name) . ' ';
			if ($value === null)
			{
				if ($this->comparison == '=')
				{
					$this->condition .= ' IS NULL';
				}
				else // if ($this->comparison == '<>')
				{
					$this->condition .= ' IS NOT NULL';
				}
				
				// todo: error out if not equality or inequality
			}
			else
			{
				$this->condition .= $this->comparison .' ' . $this->store->parseText($value);
			}
			
		}
	}
	public function visitIntProperty($name, $dirty, $value)
	{
		if($dirty)
		{
			$this->writeBracketOrAnd();
		
			$this->condition .= 't' . $this->currentTable . '.' . $this->store->fieldNamify($name) . ' ';
			if ($value === null)
			{
				if ($this->comparison == '=')
				{
					$this->condition .= ' IS NULL';
				}
				else // if ($this->comparison == '<>')
				{
					$this->condition .= ' IS NOT NULL';
				}
				
				// todo: error out if not equality or inequality
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
		
			$this->condition .= 't' . $this->currentTable . '.' . $this->store->fieldNamify($name) . ' ';
			if ($value === null)
			{
				if ($this->comparison == '=')
				{
					$this->condition .= ' IS NULL';
				}
				else // if ($this->comparison == '<>')
				{
					$this->condition .= ' IS NOT NULL';
				}
				
				// todo: error out if not equality or inequality
			}
			else
			{
				$this->condition .= $this->comparison .' ' . $this->store->parseFloat($value);
			}
		}
	}
	public function visitDatetimeProperty($name, $dirty, $value)
	{
		if($dirty)
		{
			$this->writeBracketOrAnd();
		
			$this->condition .= 't' . $this->currentTable . '.' . $this->store->fieldNamify($name) . ' ';
			if ($value === null)
			{
				if ($this->comparison == '=')
				{
					$this->condition .= ' IS NULL';
				}
				else // if ($this->comparison == '<>')
				{
					$this->condition .= ' IS NOT NULL';
				}
				
				// todo: error out if not equality or inequality
			}
			else
			{
				$this->condition .= $this->comparison .' ' . $this->store->parseDatetime($value);
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
}

?>