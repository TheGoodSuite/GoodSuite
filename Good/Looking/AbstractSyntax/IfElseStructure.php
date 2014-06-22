<?php

namespace Good\Looking\AbstractSyntax;

use Good\Looking\Grammar;
use Good\Looking\Environment;

class IfElseStructure extends ElementWithStatements
{
    private $condition;
    private $statements;
    private $elseStatements;
    
    public function __construct($grammar, $condition, $statements, $elseStatements)
    {
        parent::__construct($grammar);
        
        $this->condition = $condition;
        $this->statements = $statements;
        $this->elseStatements = $elseStatements;
    }
    
    public function execute(Environment $environment)
    {
        $out = 'if (' . $this->evaluate($this->condition, $environment) . '):';
        
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