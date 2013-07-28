<?php

namespace Good\Rolemodel\Schema;

use Good\Rolemodel\Visitor;

class ReferenceMember extends Member
{
	private $value;
	
	public function __construct($attributes, $name, $value)
	{
		parent::__construct($attributes, $name);
		
		$this->value = $value;
	}
	
	public function accept(Visitor $visitor)
	{
		// visit this, there are no children to pass visitor on to
		$visitor->visitReferenceMember($this);
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