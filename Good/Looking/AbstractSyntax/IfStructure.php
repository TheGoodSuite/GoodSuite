<?php

namespace Good\Looking\AbstractSyntax;

use Good\Looking\Grammar;

class IfStructure extends ElementWithStatements
{
    private $condition;
    private $statements;
    
    public function __construct($condition, $statements)
    {
        parent::__construct();
        
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