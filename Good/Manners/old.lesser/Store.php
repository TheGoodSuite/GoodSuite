<?php

require_once 'Storable.php';
require_once 'Condition.php';
require_once 'ValidationToken.php';

abstract class GoodMannersStore
{
	abstract protected function doModify(GoodMannersCondition $condition, GoodMannersStorable &$modifications);
	abstract protected function doGet(GoodMannersCondition $condition, GoodMannersStorable $resolver);
	abstract protected function saveNew(array &$entries);
	abstract protected function saveModifications(array &$entries);
	abstract protected function saveDeletions(array &$entries);
	
	protected function doInsert(GoodMannersStorable &$storable) {}
	
	private $validationToken;
	private $dirties;
	
	public function __construct()
	{
		$this->validationToken = new GoodMannersValidationToken();
		
		$this->dirties = array();
	}
	
	protected function invalidate()
	{
		$this->validationToken->invalidate();
		$this->validationToken = new ValidationToken();
	}
	
	public function insert(GoodMannersStorable &$storable)
	{
		$storable->setStore($this);
		$storable->setValidationToken($this->validationToken);
		
		$this->dirties[] =& $storable;
		
		$this->doInsert($storable);
	}
	
	public function flush()
	{
		$deleted = array();
		$modified = array();
		$new = array();
		
		foreach ($this->dirties as &$dirty)
		{
			if ($dirty->isDeleted() && !$dirty->isNew())
			{
				$deleted[] =& $dirty;
			}
			else if ($dirty->isNew() && !$dirty->isDeleted())
			{
				$new[] =& $dirty;
			}
			else if (!$dirty->isNew())
			{
				$modified[] =& $dirty;
			}
		}
		
		if (count($new) > 0)
		{
			$this->saveNew($new);
		}
		
		if (count($modified) > 0)
		{
			$this->saveModifications($modified);
		}
		
		if (count($deleted) > 0)
		{
			$this->saveDeletions($deleted);
		}
	}
	
	public function dirty(GoodMannersStorable &$storable)
	{
		if (!$storable->isBlank())
		{
			$this->dirties =& $storable;
		}
	}
	
	public function modify(GoodMannersCondition $condition, GoodMannersStorable &$modifications)
	{
		$this->invalidate();
		
		$this->doModify($condition, $modifications);
	}
	
	public function get(GoodMannersCondition $condition, GoodMannersStorable $resolver)
	{
		return $this->doGet($condition, $resolver);
	}
}

?>