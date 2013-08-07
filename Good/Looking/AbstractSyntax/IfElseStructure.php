<?php

namespace Good\Looking\AbstractSyntax;

use Good\Looking\Regexes;

class IfElseStructure extends ElementWithStatements
{
    private $condition;
    private $statements;
    private $elseStatements;
    
    public function __construct($condition, $statements, $elseStatements)
    {
        if (\preg_match('/^\s*' . Regexes::$expression . '\s*$/', $condition) === 0)
        {
            throw new \Exception('Error: Unable to parse if condition.');
        }
        
        $this->condition = $condition;
        $this->statements = $statements;
        $this->elseStatements = $elseStatements;
    }
    
    public function execute(Environment $environment)
    {
        $out = 'if (' . $this->evaluate($this->condition) . '):';
        
        foreach ($this->statements as $statement)
        {
            $out .= $statement->execute($environment);
        }
        
        $out .= 'else: ';
        
        foreach ($this->elseStatements as $statement)
        {
            $out .= $statement->execute($environment);
        }
        
        $out .= 'endif; ';
        
        return $out;
    }
}

?>