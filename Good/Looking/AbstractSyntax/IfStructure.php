<?php

namespace Good\Looking\AbstractSyntax;

class IfStructure extends ElementWithStatements
{
	private $condition;
	private $statements;
	
	public function __construct($condition, $statements)
	{
		if (\preg_match('/^\s*' . \Good\Looking\Regexes::$expression . '\s*$/', $condition) === 0)
		{
			die('Error: Unable to parse if condition.');
		}
		
		$this->condition = $condition;
		$this->statements = $statements;
	}
	
	public function execute(Environment $environment)
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