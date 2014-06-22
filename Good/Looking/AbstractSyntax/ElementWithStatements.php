<?php

namespace Good\Looking\AbstractSyntax;

use Good\Looking\Grammar;
use Good\Looking\Environment;

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
    
    protected function evaluate($evaluateString, Environment $environment)
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
        
        if (\preg_match('/^\\s*(?P<assignment>' . $this->grammar->variableAssignment . '(?!\\=))/', $evaluateString, $matches) === 1)
        {
            $vars = 'array("' . $matches['assignVarName'] . '"';
            $value = substr($evaluateString, strlen($matches['assignment']));
            
            while (\preg_match('/^\\s*(?P<assignment>' . $this->grammar->variableAssignment . ')/', $value, $matches) === 1)
            {
                $vars .= ', "' . $matches['assignVarName'] . '"';
                $value = substr($value, strlen($matches['assignment']));
            }
            
            $vars .= ')';
            
            return '$this->setVars(' . $vars . ', ' . $this->evaluate($value, $environment) . ')';
        }
        
        $output = '';
        
        while ($evaluateString != '')
        {
            if (\preg_match('/^\s*' . $this->grammar->term . 
                    '\s*(?P<op>(?P>operator))?\s*/', $evaluateString, $matches) == 0)
            {
                throw new \Exception("Syntax Error" . $evaluateString);
            }
            
            $evaluateString = \preg_replace('/^\s*' . $this->grammar->term . 
                    '\s*(?P<op>(?P>operator))?\s*/', '', $evaluateString);
            
            $term = $matches['term'];
            $operator = \array_key_exists('op', $matches) ? $matches['op'] : '';
            
            if (\preg_match('/^\(' . $this->grammar->expression . '\)$/', $term) != 0)
            {
                $output .= '(' . $this->evaluate(substr($term, 1, -1), $environment) . ')';
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
                
                $varModifiers = $matches['varModifiers'];
                
                while (preg_match('/^\\s*$/', $varModifiers) != 1)
                {
                    $array = '/^\s*(?P<array>' . $this->grammar->arrayAccess . ')/';
                    $property = '/^\s*(?P<property>' . $this->grammar->propertyAccess . ')/';
                    
                    
                    if (preg_match($array, $varModifiers, $matches) == 1) 
                    {
                        $templateVariable = '$this->arrayItem(' . $templateVariable . ', ' .
                                    $this->evaluate($matches['expression'], $environment) . ')';
                        
                        $varModifiers = \preg_replace($array, '', $varModifiers);
                    }
                    else if (preg_match($property, $varModifiers, $matches) == 1)
                    {
                        $templateVariable = $templateVariable . '->' . $matches['propertyName'];
                        
                        $varModifiers = \preg_replace($property, '', $varModifiers);
                    }
                    else
                    {
                        throw new \Exception("Internal Parser error");
                    }
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
            else if (preg_match('/^' . $this->grammar->func . '$/', $term, $matches) != 0)
            {
                $args = $matches['arguments'];
                $functionName = $matches['functionName'];
                
                $output .= '$this->functionHandlers["' . 
                             \addslashes($environment->getFunctionHandlerForFunction($functionName)) . 
                             '"]->handleFunction("' . $functionName . '", $this->functionHelper, array(';
                
                $first = true;
                
                while ($args != '')
                {
                    if ($first)
                    {
                        $first = false;
                    }
                    else
                    {
                        $output .= ',';
                    }
                    
                    \preg_match('/^' . $this->grammar->expression . '(?:,|$)/', $args, $matches);
                    
                    $args = \substr($args, strlen($matches[0]));
                    
                    $output .= $this->evaluate($matches['expression'], $environment);
                }
                
                $output .= '))';
            }
            else if (preg_match('/^' . $this->grammar->arrayLiteral. '$/', $term, $matches) != 0)
            {
                $output .= 'array(';
                
                if (array_key_exists('arrayItems', $matches))
                {
                    $items = $matches['arrayItems'];
                    $first = true;
                    
                    while ($items != '')
                    {
                        if ($first)
                        {
                            $first = false;
                        }
                        else
                        {
                            $output .= ',';
                        }
                        
                        preg_match('/^' . $this->grammar->expression . '(?:,|$)/', $items, $matches);
                        
                        $items = substr($items, strlen($matches[0]));
                        
                        $output .= $this->evaluate($matches['expression'], $environment);
                    }
                }
                
                $output .= ')';
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
                    
                case '==':
                    $output .= ' == ';
                    break;
                    
                case '===':
                    $output .= ' === ';
                    break;
                    
                case '!=':
                    $output .= ' != ';
                    break;
                    
                case '!==':
                    $output .= ' !== ';
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
                    
                case '%':
                    $output .= ' % ';
                    break;
             
            }
        }
        
        return $output;
        
    } // evaluate
}

?>
