<?php

require_once dirname(__FILE__) . '/../Manners/Report/ValueVisitor.php';
require_once dirname(__FILE__) . '/../Manners/Condition.php';
require_once dirname(__FILE__) . '/SQLStore.php';

class GoodMemorySQLConditionWriter implements ValueVisitor
{
	private $store;
	private $comparison;
	private $condition;
	private $first;
	
	private $currentTable;
	private $currentReference;
	private $lastTo;
	
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
		$store->setCurrentConditionWriter($this);
		
		$condition->process($this);
	}
	
	public function writeComparisonCondition(GoodMannersStorable $to, $comparison)
	{
		$store->setCurrentConditionWriter($this);
		
		$this->lastTo = $to;
		$this->comparison = $comparison;
		$this->first = true;
		$this->condition = '';
		
		$to->acceptMembers($this);
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
	
	public function visitReferenceValue(GoodMannersReferenceValue $value)
	{
		if ($typeisDirty())
		{
			$this->writeBracketOrAnd();
			
			if($type->isNull())
			{
				$this->condition .= 't' . $this->currentTable . '.' . $this->store->fieldNamify($value->getName()) . 
											$this->comparison . ' NULL';
			}
			else if (!$type->getOriginal()->isBlank())
			{
				$this->condition .= 't' . $this->currentTable . '.' . $this->store->fieldNamify($value->getName()) . 
											$this->comparison . ' ' . intval($value->getOriginal()->getId());
			}
			else
			{
				$join = $this->store->getJoin($this->currentTable, $this->currentReference);
				
				if ($join == -1)
				{
					$join = $this->store->createJoin($this->currentTable, $value->getName(), $this->currentReference, $value->getClassName());
				}
				
				$subWriter = new GoodMemorySQLConditionWriter($this->store, $join);
				$subWriter->writeComparisonCondition($this->lastTo, $this->comparison);
				
				$this->store->setCurrentConditionWriter($this);
				
				$this->condition .= '(' . $subWriter->getCondition() . ')';
			}
		}
		
		$this->currentReference++;
	}
	
	public function visitTextValue(GoodMannersTextValue $value)
	{
		$this->writeBracketOrAnd();
		
		$this->condition .= 't' . $this->currentTable . '.' . $this->store->fieldNamify($value->getName()) . 
								$this->comparison . ' ' . $this->store->parseText($value);
	}
	public function visitIntValue(GoodMannersIntValue $value)
	{
		$this->writeBracketOrAnd();
		
		$this->condition .= 't' . $this->currentTable . '.' . $this->store->fieldNamify($value->getName()) .
								$this->comparison . ' ' . $this->store->parseInt($value);
	}
	public function visitFloatValue(GoodMannerwsFloatValue $value)
	{
		$this->writeBracketOrAnd();
		
		$this->condition .= 't' . $this->currentTable . '.' . $this->store->fieldNamify($value->getName()) .
								$this->comparison . ' ' . $this->store->parseFloat($value);
	}
	
	private function writeBracketOrAnd()
	{
		if ($this->first)
		{
			$this->condition = '(';
			$this->first = false;
		}
		else
		{
			$this->condition .= ' AND ';
		}
	}
}

?>