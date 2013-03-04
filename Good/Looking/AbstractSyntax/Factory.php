<?php

class GoodLookingAbstractSyntaxFactory
{
	public function createAbstractDocument($statements)
	{
		return new GoodLookingAbstractDocument($statements);
	}
	
	public function createTextBlock($text)
	{
		return new GoodLookingTextBlock($text);
	}
	
	public function createStatement($code)
	{
		return new GoodLookingStatement($code);
	}
	
	public function createIfStructure($condition, $statements)
	{
		return new GoodLookingIfStructure($condition, $statements);
	}
	
	public function createIfElseStructure($condition, $statements, $elseStatements)
	{
		return new GoodLookingIfElseStructure($condition, $statements, $elseStatements);
	}
	
	public function createForStructure($condition, $statements)
	{
		return new GoodLookingForStructure($condition, $statements);
	}
	
	public function createForeachStructure($condition, $statements)
	{
		return new GoodLookingForeachStructure($condition, $statements);
	}
}

?>