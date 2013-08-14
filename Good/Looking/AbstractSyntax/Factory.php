<?php

namespace Good\Looking\AbstractSyntax;

class Factory
{
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
        return new Statement($code);
    }
    
    public function createIfStructure($condition, $statements)
    {
        return new IfStructure($condition, $statements);
    }
    
    public function createIfElseStructure($condition, $statements, $elseStatements)
    {
        return new IfElseStructure($condition, $statements, $elseStatements);
    }
    
    public function createForStructure($from, $to, $statements)
    {
        return new ForStructure($from, $to, $statements);
    }
    
    public function createForeachStructure($array, $varName, $statements)
    {
        return new ForeachStructure($array, $varName, $statements);
    }
}

?>