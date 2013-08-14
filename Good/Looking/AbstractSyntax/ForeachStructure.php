<?php

namespace Good\Looking\AbstractSyntax;

use Good\Looking\Grammar;

class ForeachStructure extends ElementWithStatements
{
    private $varName;
    private $arrayStatement;
    private $statements;
    
    public function __construct($array, $varName, $statements)
    {
        parent::__construct();
        
        $this->statements = $statements;
        $this->arrayStatement = $array;
        $this->varName = $varName;
    }
    
    public function execute(Environment $environment)
    {
        $counter = $environment->getTemplateVar();
        
        $out  = '$this->registerSpecialVar("' . $this->varName . '", ' . $counter . '); ';
        $out .= 'foreach(' . $this->evaluate($this->arrayStatement) . ' as $this->templateVars[' . $counter . ']): ';
        
        foreach ($this->statements as $statement)
        {
            $out .= $statement->execute($environment);
        }
        
        $out .= 'endforeach; ';
        
        return $out;
    }
}

?>