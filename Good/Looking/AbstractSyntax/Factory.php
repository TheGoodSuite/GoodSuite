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
	
	public function createForStructure($condition, $statements)
	{
		return new ForStructure($condition, $statements);
	}
	
	public function createForeachStructure($condition, $statements)
	{
		return new ForeachStructure($condition, $statements);
	}
}

?>