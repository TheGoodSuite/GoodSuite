<?php

namespace Good\Looking\AbstractSyntax;

use Good\Looking\Grammar;
use Good\Looking\Environment;

class ForeachStructure extends ElementWithStatements
{
    private $varName;
    private $arrayStatement;
    private $statements;
    
    public function __construct($grammar, $array, $varName, $statements)
    {
        parent::__construct($grammar);
        
        $this->statements = $statements;
        $this->arrayStatement = $array;
        $this->varName = $varName;
    }
    
    public function execute(Environment $environment)
    {
        $out  = '$this->checkVarName("' . $this->varName . '"); ';
        $out .= 'foreach(' . $this->evaluate($this->arrayStatement, $environment) . ' as $this->automaticVars["' . $this->varName . '"]): ';
        
        foreach ($this->statements as $statement)
        {
            $out .= $statement->execute($environment);
        }
        
        $out .= 'endforeach; $this->freedAutomaticVars["' . $this->varName . '"] = true;';
        
        return $out;
    }
}

?>