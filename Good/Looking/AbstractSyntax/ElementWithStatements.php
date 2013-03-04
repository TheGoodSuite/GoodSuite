<?php

// This class provides the functionality of parsing statements to child classes
// This class exists only because out parser only partally parses and leaves
// statements as strings. This should be fixed in the future, making sure
// this class will no longer be needed.

abstract class GoodLookingAbstractSyntaxElementWithStatements implements GoodLookingAbstractSyntaxElement
{
	protected function evaluate($evaluateString)
	{
		//  w00t! finally did this function
		
		if (preg_match('/^\s*$/', $evaluateString) != 0)
		{
			return '';
		}
		
		if (preg_match('/' . GoodLookingRegexes::$expression . '/', $evaluateString) == 0)
		{
			die("Syntax error");
		}
		
		$output = '';
		
		while ($evaluateString != '')
		{
			if (preg_match('/^\s*' . GoodLookingRegexes::$term . 
					'\s*(?P<op>(?P>operator))?\s*/', $evaluateString, $matches) == 0)
			{
				die("Syntax Error");
			}
			
			$evaluateString = preg_replace('/^\s*' . GoodLookingRegexes::$term . 
					'\s*(?P<op>(?P>operator))?\s*/', '', $evaluateString);
			
			$term = $matches['term'];
			$operator = array_key_exists('op', $matches) ? $matches['op'] : '';
			
			if (preg_match('/^\(' . GoodLookingRegexes::$expression . '\)$/', $term) != 0)
			{
				$output .= '(' . $this->evaluate($term) . ')';
			}
			else if (preg_match('/^' . GoodLookingRegexes::$literalBoolean . '$/',
														$term, $matches) != 0)
			{
				$output .= $matches['boolean'];
			}
			else if (preg_match('/^' . GoodLookingRegexes::$variable . '$/', 
														$term, $matches) != 0)
			{
				$templateVariable = '$this->getVar(\'' . $matches['varName'] . '\')';
				
				$arrayItemSelector = $matches['arrayItemSelector'];
				
				while ($arrayItemSelector != '')
				{
					preg_match('/^\[' . GoodLookingRegexes::$expression . '\]/',
											$arrayItemSelector, $matches);
					$arrayItemSelector = preg_replace('/^\[' 
												. GoodLookingRegexes::$expression . '\]/',
												 '', $arrayItemSelector);
					
					$templateVariable = '$this->arrayItem(' . $templateVariable . ', ' .
									$this->evaluate($matches['expression']) . ')';
				}
				
				$output .= $templateVariable;
			}
			else if (preg_match('/^' . GoodLookingRegexes::$literalString . '$/', $term) != 0)
			{
				$output .= $term;
			}
			else if (preg_match('/^' . GoodLookingRegexes::$literalNumber . '$/', $term) != 0)
			{
				$output .= $term;
			}
			else if (preg_match('/^' . GoodLookingRegexes::$func . '$/', $term) != 0)
			{
				// as of yet, functions are unsupported
				die("Function call found while functions are currently unsupported");
			}
			else
			{
				die("Could not qualify term as any type of term!");
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
