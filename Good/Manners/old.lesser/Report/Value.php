<?php

include_once 'Visitor.php';

abstract class GoodMannersValue
{
	abstract public function accept(GoodRolemodelVisitor $visitor);
	
	private $isNull;
	private $dirty;
	private $name;
	
	public function __construct($isNull, $dirty, $name)
	{
		$this->isNull = $isNull;
		$this->dirty = $dirty;
		$this->name = $name;
	}
	
	public function isNull()
	{
		return $this->isNull;
	}
	
	public function isDirty()
	{
		return $this->dirty;
	}
	
	public function getName()
	{
		return $this->name;
	}
}

?>