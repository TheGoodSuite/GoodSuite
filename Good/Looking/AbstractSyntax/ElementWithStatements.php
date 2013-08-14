<?php

namespace Good\Looking\AbstractSyntax;

use Good\Looking\Grammar;

// This class provides the functionality of parsing statements to child classes
// This class exists only because out parser only partally parses and leaves
// statements as strings. This should be fixed in the future, making sure
// this class will no longer be needed.

abstract class ElementWithStatements implements Element
{
    private $grammar;
    
    public function __construct(Grammar $grammar)
    {
        $this->grammar = $grammar;
    }
    
    protected function evaluate($evaluateString)
    {
        //  w00t! finally did this function
        
        if (\preg_match('/^\s*$/', $evaluateString) != 0)
        {
            throw new Exception('Empty statement is not a statement at all.');
        }
        
        if (\preg_match('/' . $this->grammar->expression . '/', $evaluateString) == 0)
        {
            throw new \Exception("Syntax error");
        }
        
        $output = '';
        
        while ($evaluateString != '')
        {
            if (\preg_match('/^\s*' . $this->grammar->term . 
                    '\s*(?P<op>(?P>operator))?\s*/', $evaluateString, $matches) == 0)
            {
                throw new \Exception("Syntax Error");
            }
            
            $evaluateString = \preg_replace('/^\s*' . $this->grammar->term . 
                    '\s*(?P<op>(?P>operator))?\s*/', '', $evaluateString);
            
            $term = $matches['term'];
            $operator = \array_key_exists('op', $matches) ? $matches['op'] : '';
            
            if (\preg_match('/^\(' . $this->grammar->expression . '\)$/', $term) != 0)
            {
                $output .= '(' . $this->evaluate(substr($term, 1, -1)) . ')';
            }
            else if (\preg_match('/^' . $this->grammar->literalBoolean . '$/',
                                                        $term, $matches) != 0)
            {
                $output .= $matches['boolean'];
            }
            else if (\preg_match('/^' . $this->grammar->variable . '$/', 
                                                        $term, $matches) != 0)
            {
                $templateVariable = '$this->getVar(\'' . $matches['varName'] . '\')';
                
                $arrayItemSelector = $matches['arrayItemSelector'];
                
                while ($arrayItemSelector != '')
                {
                    \preg_match('/^\[' . $this->grammar->expression . '\]/',
                                            $arrayItemSelector, $matches);
                    $arrayItemSelector = \preg_replace('/^\[' 
                                                . $this->grammar->expression . '\]/',
                                                 '', $arrayItemSelector);
                    
                    $templateVariable = '$this->arrayItem(' . $templateVariable . ', ' .
                                    $this->evaluate($matches['expression']) . ')';
                }
                
                $output .= $templateVariable;
            }
            else if (preg_match('/^' . $this->grammar->literalString . '$/', $term) != 0)
            {
                $output .= $term;
            }
            else if (preg_match('/^' . $this->grammar->literalNumber . '$/', $term) != 0)
            {
                $output .= $term;
            }
            else if (preg_match('/^' . $this->grammar->func . '$/', $term) != 0)
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
                    
                case 'xor':
                    $output .= ' xor ';
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
