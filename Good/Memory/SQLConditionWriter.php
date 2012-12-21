<?php

require_once dirname(__FILE__) . '/PropertyVisitor.php';
require_once dirname(__FILE__) . '/../Manners/Condition.php';
require_once dirname(__FILE__) . '/ConditionProcessor.php';

class GoodMemorySQLConditionWriter implements GoodMemoryPropertyVisitor,
											  GoodMemoryConditionProcessor
{
	private $store;
	private $comparison;
	private $condition;
	private $first;
	
	private $currentTable;
	private $currentReference;
	
	public function __construct(GoodMemorySQLStore $store, $currentTable)
	{	
		$this->store = $store;
		$this->currentTable = $currentTable;
	}
	
	public function getCondition()
	{
		return $this->condition;
	}
	
	public function writeCondition(GoodMannersCondition $condition)
	{
		$this->store->setCurrentConditionProcessor($this);
		
		$condition->process($this->store);
	}
	
	public function writeComparisonCondition(GoodMannersStorable $to, $comparison)
	{
		$this->store->setCurrentConditionProcessor($this);
		
		$this->comparison = $comparison;
		$this->first = true;
		$this->condition = '';
		
		$this->store->setCurrentPropertyVisitor($this);
		$to->acceptStore($this->store);
		
		if ($this->first)
		{
			$this->condition = '1 = 1';
		}
	}
	
	public function processEqualityCondition(GoodMannersStorable $to)
	{
		$this->writeComparisonCondition($to, '=');
	}
	public function processInequalityCondition(GoodMannersStorable $to)
	{
		$this->writeComparisonCondition($to, '<>');
	}
	public function processGreaterCondition(GoodMannersStorable $to)
	{
		$this->writeComparisonCondition($to, '>');
	}
	public function processGreaterOrEqualsCondition(GoodMannersStorable $to)
	{
		$this->writeComparisonCondition($to, '>=');
	}
	public function processLessCondition(GoodMannersStorable $to)
	{
		$this->writeComparisonCondition($to, '<');
	}
	public function processLessOrEqualsCondition(GoodMannersStorable $to)
	{
		$this->writeComparisonCondition($to, '<=');
	}
	
	public function processAndCondition(GoodMannersCondition $condition1, GoodMannersCondition $condition2)
	{
		$this->writeCondition($condition1);
		$sqlCondition1 = $this->getCondition();
		
		$this->writeCondition($condition2);
		$sqlCondition2 = $this->getCondition();
		
		$this->condition = '(' . $sqlCondition1 . ' AND ' . $sqlCondition2 . ')';
	}
	public function processOrCondition(GoodMannersCondition $condition1, GoodMannersCondition $condition2)
	{
		$this->writeCondition($condition1);
		$sqlCondition1 = $this->getCondition();
		
		$this->writeCondition($condition2);
		$sqlCondition2 = $this->getCondition();
		
		$this->condition = '(' . $sqlCondition1 . ' OR ' . $sqlCondition2 . ')';
	}
	
	public function visitReferenceProperty($name, $datatypeName, $dirty, $null, 
															GoodMannersStorable $value = null)
	{
		if ($dirty)
		{
			$this->writeBracketOrAnd();
			
			if($null)
			{
				$this->condition .= 't' . $this->currentTable . '.' . $this->store->fieldNamify($name) . 
											$this->comparison . ' NULL';
			}
			else if (!$value->isNew())
			{
				$this->condition .= 't' . $this->currentTable . '.' . $this->store->fieldNamify($name) . 
											$this->comparison . ' ' . intval($value->getId());
			}
			else
			{
				$join = $this->store->getJoin($this->currentTable, $this->currentReference);
				
				if ($join == -1)
				{
					$join = $this->store->createJoin($this->currentTable, $name, $this->currentReference, $datatypeName);
				}
				
				$subWriter = new GoodMemorySQLConditionWriter($this->store, $join);
				$subWriter->writeComparisonCondition($value, $this->comparison);
				
				$this->store->setCurrentConditionProcessor($this);
				$this->condition .= $subWriter->getCondition();
			}
		}
		
		$this->currentReference++;
	}
	
	public function visitTextProperty($name, $dirty, $null, $value)
	{
		if($dirty)
		{
			$this->writeBracketOrAnd();
		
			$this->condition .= 't' . $this->currentTable . '.' . $this->store->fieldNamify($name) .
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
		
			$this->condition .= 't' . $this->currentTable . '.' . $this->store->fieldNamify($name) .
									' ' . $this->comparison;
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
			
			$this->condition .= 't' . $this->currentTable . '.' . $this->store->fieldNamify($name) .
									' ' . $this->comparison;
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