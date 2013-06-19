<?php

namespace Good\Looking\AbstractSyntax;

use Good\Looking\Regexes;

// This class provides the functionality of parsing statements to child classes
// This class exists only because out parser only partally parses and leaves
// statements as strings. This should be fixed in the future, making sure
// this class will no longer be needed.

abstract class ElementWithStatements implements Element
{
	protected function evaluate($evaluateString)
	{
		//  w00t! finally did this function
		
		if (\preg_match('/^\s*$/', $evaluateString) != 0)
		{
			return '';
		}
		
		if (\preg_match('/' . Regexes::$expression . '/', $evaluateString) == 0)
		{
			throw new \Exception("Syntax error");
		}
		
		$output = '';
		
		while ($evaluateString != '')
		{
			if (\preg_match('/^\s*' . Regexes::$term . 
					'\s*(?P<op>(?P>operator))?\s*/', $evaluateString, $matches) == 0)
			{
				throw new \Exception("Syntax Error");
			}
			
			$evaluateString = \preg_replace('/^\s*' . Regexes::$term . 
					'\s*(?P<op>(?P>operator))?\s*/', '', $evaluateString);
			
			$term = $matches['term'];
			$operator = \array_key_exists('op', $matches) ? $matches['op'] : '';
			
			if (\preg_match('/^\(' . Regexes::$expression . '\)$/', $term) != 0)
			{
				$output .= '(' . $this->evaluate($term) . ')';
			}
			else if (\preg_match('/^' . Regexes::$literalBoolean . '$/',
														$term, $matches) != 0)
			{
				$output .= $matches['boolean'];
			}
			else if (\preg_match('/^' . Regexes::$variable . '$/', 
														$term, $matches) != 0)
			{
				$templateVariable = '$this->getVar(\'' . $matches['varName'] . '\')';
				
				$arrayItemSelector = $matches['arrayItemSelector'];
				
				while ($arrayItemSelector != '')
				{
					\preg_match('/^\[' .Regexes::$expression . '\]/',
											$arrayItemSelector, $matches);
					$arrayItemSelector = \preg_replace('/^\[' 
												. Regexes::$expression . '\]/',
												 '', $arrayItemSelector);
					
					$templateVariable = '$this->arrayItem(' . $templateVariable . ', ' .
									$this->evaluate($matches['expression']) . ')';
				}
				
				$output .= $templateVariable;
			}
			else if (preg_match('/^' . Regexes::$literalString . '$/', $term) != 0)
			{
				$output .= $term;
			}
			else if (preg_match('/^' . Regexes::$literalNumber . '$/', $term) != 0)
			{
				$output .= $term;
			}
			else if (preg_match('/^' . Regexes::$func . '$/', $term) != 0)
			{
				// as of yet, functions are unsupported
				throw new \Exception("Function call found while functions are currently unsupported");
			}
			else
			{
				throw new \Exception("Could not qualify term as any type of term!");
			}
			
			switch ($operator)
			{
				case '+':
					$output .= ' + ';
					break;
					
				case '-':
					$output .= ' - ';
					break;
					
				case '/':
					$output .= ' / ';
					break;
					
				case '*':
					$output .= ' * ';
					break;
					
				case '||':
				case 'or':
					$output .= ' || ';
					break;
					
				case '&&':
				case 'and':
					$output .= ' && ';
					break;
					
				case '=':
				case '==':
					$output .= ' == ';
					break;
					
				case '!=':
					$output .= ' != ';
					break;
					
				case '>':
					$output .= ' > ';
					break;
					
				case '<':
					$output .= ' < ';
					break;
					
				case '>=':
					$output .= ' >= ';
					break;
					
				case '<=':
					$output .= ' <= ';
					break;
					
				case '.':
					$output .= ' . ';
					break;
			 
			}
		}
		
		return $output;
		
	} // evaluate
}

?>