<?php

include_once 'Value.php';

class GoodMannersIntValue extends GoodMannersValue
{
	private $value;
	
	public function __construct($isNull, $dirty, $name, $value)
	{
		parent::__construct($isNull, $dirty, $name);
		
		$this->value = $value;
	}
	
	public function accept(GoodRolemodelVisitor $visitor)
	{
		$visitor->visitIntValue($this);
	}
	
	public function getValue()
	{
		return $value;
	}
}

?>