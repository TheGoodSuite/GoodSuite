<?php

include_once 'Value.php';

class GoodMannersReferenceValue extends GoodMannersValue
{
	private $members;
	private $className;
	private $original;
	private $resolved;
	
	public function __construct($isNull, $dirty, $name, $resolved, &$original, $className, array $members)
	{
		parent::__construct($isNull, $dirty, $name);
		
		$this->members = $members;
		$this->resolved = $resolved;
		$this->original =& $original;
		$this->className = $className;
	}
	
	public function accept(GoodRolemodelVisitor $visitor)
	{
		$visitor->visitReferenceValue($this);
	}
	
	public function acceptMembers(GoodRolemodelVisitor $visitor)
	{
		foreach ($this->members as $member)
		{
			$member->accept($visitor);
		}
	}
	
	public function getClassName()
	{
		return $this->className;
	}
	
	public &getOriginal()
	{
		return $this->original;
	}
	
	public isResolved()
	{
		return $this->resolved;
	}
}

?>