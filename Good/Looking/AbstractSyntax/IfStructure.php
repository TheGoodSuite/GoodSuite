<?php

class GoodLookingIfStructure extends GoodLookingAbstractSyntaxElementWithStatements
{
	private $condition;
	private $statements;
	
	public function __construct($condition, $statements)
	{
		if (preg_match('/^\s*' . GoodLookingRegexes::$expression . '\s*$/', $condition) === 0)
		{
			die('Error: Unable to parse if condition.');
		}
		
		$this->condition = $condition;
		$this->statements = $statements;
	}
	
	public function execute(GoodLookingEnvironment $environment)
	{
		$out = 'if (' . $this->evaluate($this->condition) . '):';
		
		foreach ($this->statements as $statement)
		{
			$out .= $statement->execute($environment);
		}
		
		$out .= 'endif; ';
		
		return $out;
	}
}

?>