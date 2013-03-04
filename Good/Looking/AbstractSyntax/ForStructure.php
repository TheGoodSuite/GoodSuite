<?php

namespace Good\Looking\AbstractSyntax;

class ForStructure extends ElementWithStatements
{
	private $term1;
	private $term2;
	private $statements;
	
	public function __construct($condition, $statements)
	{
		if (\preg_match('/^\s*' . \Good\Looking\Regexes::$controlStructureConditions['for'] . 
															'\s*$/', $condition, $matches) !== 1)
		{
			die('Error: Unable to parse for condition.');
		}
		
		if (\preg_match('/^s*' . \Good\Looking\Regexes::$expression . '\s*$/', $matches['term1']) !== 1)
		{
			die('Error: first term in for condition is invalid.');
		}
		
		if (\preg_match('/^s*' . \Good\Looking\Regexes::$expression . '\s*$/', $matches['term2']) !== 1)
		{
			die('Error: first term in for condition is invalid.');
		}
		
		$this->statements = $statements;
		$this->term1 = $matches['term1'];
		$this->term2 = $matches['term2'];
	}
	
	public function execute(Environment $environment)
	{
		$counter = $environment->getTemplateVar();
		$from = $environment->getTemplateVar();
		$to = $environment->getTemplateVar();
		$delta = $environment->getTemplateVar();
		
		$out  = '$this->templateVars[' . $from . '] = ' . $this->evaluate($this->term1) . '; ';
		$out .= '$this->templateVars[' . $to . '] = ' . $this->evaluate($this->term2) . '; ';
		$out .= '$this->templateVars[' . $delta . '] = $this->templateVars[' . $from . '] < ' .
					'$this->templateVars[' . $to . '] ? 1 : -1; ';
		$out .= 'for ($this->templateVars[' . $counter . '] = $this->templateVars[' . $from . ']; ' . 
					'$this->templateVars[' . $counter . '] * $this->templateVars[' . $delta . '] <= ' .
					  '$this->templateVars[' . $to . '] * $this->templateVars[' . $delta . ']; ' .
					   '$this->templateVars[' . $counter . '] += $this->templateVars[' . $delta . ']):';
		
		foreach ($this->statements as $statement)
		{
			$out .= $statement->execute($environment);
		}
		
		$out .= 'endfor; ';
		
		return $out;
	}
}

?>