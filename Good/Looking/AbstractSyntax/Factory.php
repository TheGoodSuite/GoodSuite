<?php

namespace Good\Looking\AbstractSyntax;

use Good\Looking\Grammar;

class Factory
{
    private $grammar;
    
    public function __construct(Grammar $grammar)
    {
        $this->grammar = $grammar;
    }
    
    public function createAbstractDocument($statements)
    {
        return new Document($statements);
    }
    
    public function createTextBlock($text)
    {
        return new TextBlock($text);
    }
    
    public function createStatement($code)
    {
        return new Statement($this->grammar, $code);
    }
    
    public function createIfStructure($condition, $statements)
    {
        return new IfStructure($this->grammar, $condition, $statements);
    }
    
    public function createIfElseStructure($condition, $statements, $elseStatements)
    {
        return new IfElseStructure($this->grammar, $condition, $statements, $elseStatements);
    }
    
    public function createForrangeStructure($from, $to, $counter, $statements)
    {
        return new ForrangeStructure($this->grammar, $from, $to, $counter, $statements);
    }
    
    public function createForeachStructure($array, $varName, $statements)
    {
        return new ForeachStructure($this->grammar, $array, $varName, $statements);
    }
}

?>