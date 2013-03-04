<?php

class GoodLookingStatement extends GoodLookingAbstractSyntaxElementWithStatements
{
	private $code;
	
	public function __construct($code)
	{
		$this->code = $code;
	}
	
	public function execute(GoodLookingEnvironment $environment)
	{
		if (preg_match('/^\s*$/', $this->code) === 1)
		{
			return '';
		}
		
		return 'echo htmlentities(' . $this->evaluate($this->code) . '); ';
	}
}

?>