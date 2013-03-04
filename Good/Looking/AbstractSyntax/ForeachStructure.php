<?php

namespace Good\Looking\AbstractSyntax;

class ForeachStructure extends ElementWithStatements
{
	private $varName;
	private $arrayStatement;
	private $statements;
	
	public function __construct($condition, $statements)
	{
		if (\preg_match('/^\s*' . \Good\Looking\Regexes::$controlStructureConditions['foreach'] . 
															'\s*$/', $condition, $matches) !== 1)
		{
			die('Error: Unable to parse foreach condition.');
		}
		
		if (\preg_match('/^s*' . \Good\Looking\Regexes::$expression . '\s*$/', $matches['array']) !== 1)
		{
			die('Error: array term in foreach condition is invalid.');
		}
		
		$this->statements = $statements;
		$this->arrayStatement = $matches['array'];
		$this->varName = $matches['varName'];
	}
	
	public function execute(Environment $environment)
	{
		$counter = $environment->getTemplateVar();
		
		$out  = '$this->registerSpecialVar("' . $this->varName . '", ' . $counter . '); ';
		$out .= 'foreach(' . $this->evaluate($this->arrayStatement) . ' as $this->templateVars[' . $counter . ']): ';
		
		foreach ($this->statements as $statement)
		{
			$out .= $statement->execute($environment);
		}
		
		$out .= 'endforeach; ';
		
		return $out;
	}
}

?>