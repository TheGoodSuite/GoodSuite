<?php

namespace Good\Looking;

class Parser
{
    protected $factory;
    protected $inTextMode;
    
    public function __construct(AbstractSyntax\Factory $factory)
    {
        $this->factory = $factory;
    }
    
    public function parseDocument($input)
    {
        $input = \preg_replace('/' . Regexes::$comment . '/', '', $input);
        
        $this->inTextMode = true;
        
        $out = $this->factory->createAbstractDocument($this->parseStatementCollection($input));
        
        if ($input !== '')
        {
            throw new \Exception('Error: Could not parse document. Near: ' . substr($input, 0, 50));
        }
        
        return $out;
    }
    
    private function parseIfStructure(&$input, $condition)
    {
        $statements = $this->parseStatementCollection($input);
        
        if (\preg_match('/^\\s*else\\s*(?<terminator>' . 
                            Regexes::$statementEnder . '|' .
                                Regexes::$scriptDelimiterRight . '|$)/', $input, $matches) === 1)
        {
            $else = true;
            
            // remove the processed part
            $this->removeFromStart($input, $matches[0]);
            
            // determine next mode
            $this->determineIfNextModeIsText($matches['terminator']);
            
            $elseStatements = $this->parseStatementCollection($input);
        }
        else
        {
            $else = false;
        }
        
        if (\preg_match('/^\\s*end\\s*if\\s*(?<terminator>' . 
                            Regexes::$statementEnder . '|' .
                                Regexes::$scriptDelimiterRight . '|$)/', $input, $matches) === 1)
        {
            // remove the processed part
            $this->removeFromStart($input, $matches[0]);
            
            // determine next mode
            $this->determineIfNextModeIsText($matches['terminator']);
            
            if ($else)
            {
                return $this->factory->createIfElseStructure($condition, $statements, $elseStatements);
            }
            else
            {
                return $this->factory->createIfStructure($condition, $statements);
            }
        }
        
        if ($input == '')
        {
            throw new \Exception('Error: End of document found though there was still an "if" that needed to be closed.');
        }
        else if (\preg_match('/^\\s*' . Regexes::$endingControlStructures . '\\s*$/', $input, $matched))
        {
            throw new \Exception('Error: Control structure mismatch, found <i>' . $matches[0] . '</i> while parsing an if.');
        }
        else
        {
            throw new \Exception('Error: Unable to parse. Near: ' . \htmlentities(substr($input, 0, 50)));
        }
    }
    
    private function parseForStructure(&$input, $condition)
    {
        $statements = $this->parseStatementCollection($input);
        
        if (\preg_match('/^\\s*end\\s*for\\s*(?<terminator>' . 
                            Regexes::$statementEnder . '|' .
                                Regexes::$scriptDelimiterRight . '|$)/', $input, $matches) === 1)
        {
            // remove the processed part
            $this->removeFromStart($input, $matches[0]);
            
            // determine next mode
            $this->determineIfNextModeIsText($matches['terminator']);
            
            return $this->factory->createForStructure($condition, $statements);
        }
        
        if ($input == '')
        {
            throw new \Exception('Error: End of document found though there was still a "for" that needed to be closed.');
        }
        else if (\preg_match('/^s*' . Regexes::$endingControlStructures, $input, $matched))
        {
            throw new \Exception('Error: Control structure mismatch, found <i>' . $matches[0] . '</i> while parsing a for.');
        }
        else
        {
            throw new \Exception('Error: Unable to parse. Near: ' . \htmlentities(substr($input, 0, 20)));
        }
    }
    
    private function parseForeachStructure(&$input, $condition)
    {
        $statements = $this->parseStatementCollection($input);
        
        if (\preg_match('/^\\s*end\\s*foreach\\s*(?<terminator>' . 
                            Regexes::$statementEnder . '|' .
                                Regexes::$scriptDelimiterRight . '|$)/', $input, $matches) === 1)
        {
            // remove the processed part
            $this->removeFromStart($input, $matches[0]);
            
            // determine next mode
            $this->determineIfNextModeIsText($matches['terminator']);
            
            return $this->factory->createForeachStructure($condition, $statements);
        }
        
        if ($input == '')
        {
            throw new \Exception('Error: End of document found though there was still a "foreach" that needed to be closed.');
        }
        else if (\preg_match('/^\\s*' . Regexes::$endingControlStructures . '\\s*$/', $input, $matched))
        {
            throw new \Exception('Error: Control structure mismatch, found <i>' . $matches[0] . '</i> while parsing a foreach.');
        }
        else
        {
            throw new \Exception('Error: Unable to parse. Near: ' . \htmlentities(substr($input, 0, 20)));
        }
    }
    
    private function removeFromStart(&$input, $needle)
    {
        if (strlen($input) > strlen($needle))
        {
            $input = substr($input, strlen($needle));
        }
        else
        {
            $input = '';
        }
    }
        
    private function determineIfNextModeIsText($terminator)
    {
        if (\preg_match('/' . Regexes::$scriptDelimiterRight . '/', $terminator) == 1)
        {
            $this->inTextMode = true;
        }
    }
    
    private function parseStatementCollection(&$input)
    {
        $statements = array();
        
        // regex for:
        // a statement or nothing anchored to the begin of $input 
        // (with whitespace in front of and behind it) followed by
        // a statement ender (;), script delimiter (:>) or the end of the string
        $regexExpression = '/^\\s*(' . Regexes::$expression . '|)\\s*(?<terminator>' . 
                                        Regexes::$statementEnder . '|' .
                                            Regexes::$scriptDelimiterRight . '|$)/';
                                            
        // Same idea as above, but for control structures
        $regexControlStructure = '/^\\s*' . Regexes::$startingControlStructures . '\\s*(?<terminator>' . 
                                            Regexes::$statementEnder . '|' .
                                                Regexes::$scriptDelimiterRight . '|$)/';
        
        $parseable = true;
        
        while ($parseable && $input != '')
        {
            while ($input !== '' && ($this->inTextMode ||
                      \preg_match($regexExpression, $input, $matches) === 1))
            {
                if ($this->inTextMode)
                {
                    // Everthing before delimiter is text
                    // Everything after is for the next iteration of parsing
                    $parts = \preg_split('/' . Regexes::$scriptDelimiterLeft . '/', $input, 2);
                    
                    if ($parts[0] != '')
                    {
                        $statements[] = $this->factory->createTextBlock($parts[0]);
                    }
                    
                    if (\array_key_exists(1, $parts))
                    {
                        $input = $parts[1];
                    }
                    else
                    {
                        $input = '';
                    }
                    
                    // Since we only stop at a script delimiter or end of input
                    // we always drop out of text a text block
                    $this->inTextMode = false;
                }
                else
                {
                    // Make a statment out of entire match except the terminating symbol 
                    // (= statement ender, script delimiter or end of input)
                    $statements[] = $this->factory->createStatement(
                                \substr($matches[0], 0, -1 * strlen($matches['terminator'])));
                    
                    // remove the processed part
                    $this->removeFromStart($input, $matches[0]);
                    
                    // determine next mode
                    $this->determineIfNextModeIsText($matches['terminator']);
                }
            }
            
            if ($input != '' && preg_match($regexControlStructure, $input, $matches) === 1)
            {
                    
                // remove the processed part
                $this->removeFromStart($input, $matches[0]);
                
                // determine next mode
                $this->determineIfNextModeIsText($matches['terminator']);
                
                if ($matches['structure'] == 'if')
                {
                    $statements[] = $this->parseIfStructure($input, $matches['condition']);
                }
                else if ($matches['structure'] == 'for')
                {
                    $statements[] = $this->parseForStructure($input, $matches['condition']);
                }
                else if ($matches['structure'] == 'foreach')
                {
                    $statements[] = $this->parseForeachStructure($input, $matches['condition']);
                }
                else
                {
                    throw new \Exception('Unrecognized Control Structure');
                }
            }
            else
            {
                $parseable = false;
            }
        }
        
        return $statements;
    }
}

?>