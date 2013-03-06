<?php

namespace Good\Rolemodel;

class TypeReference extends Type
{
	private $value;
	
	public function __construct($value)
	{
		$this->value = $value;
	}
	
	public function accept(Visitor $visitor)
	{
		// visit this, there are no children to pass visitor on to
		$visitor->visitTypeReference($this);
	}
	
	public function getReferencedType()
	{
		return $this->value;
	}
	
	public function getReferencedTypeIfAny()
	{
		return $this->value;
	}
}

?>