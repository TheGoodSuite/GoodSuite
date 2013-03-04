<?php

namespace Good\Looking;

require_once 'Regexes.php';
require_once 'AbstractSyntax/Element.php';
require_once 'AbstractSyntax/ElementWithStatements.php';
require_once 'AbstractSyntax/Document.php';
require_once 'AbstractSyntax/TextBlock.php';
require_once 'AbstractSyntax/Statement.php';
require_once 'AbstractSyntax/IfStructure.php';
require_once 'AbstractSyntax/IfElseStructure.php';
require_once 'AbstractSyntax/ForStructure.php';
require_once 'AbstractSyntax/ForeachStructure.php';
require_once 'AbstractSyntax/Factory.php';
require_once 'AbstractSyntax/Environment.php';
require_once 'Parser.php';

Class Compiler
{
	public function __construct()
	{
	} // __construct
	
	public function compile($input, $output)
	{
		$compiledTemplate = $this->compileTemplate(file_get_contents($input));
		
		$file = \fopen($output, 'w+');
		\fwrite($file, $compiledTemplate);
		\fclose($file);
		
	} // compile
	
	private function compileTemplate($input)
	{
		// for testing I really want to know how long this function takes to be executed
		$executionTime = \microtime(true);
		
		$factory = new AbstractSyntax\Factory();
		$parser = new Parser($factory);
		
		$document = $parser->parseDocument($input);
		
		$environment = new AbstractSyntax\Environment();
		
		$output = $document->execute($environment);
		
		// for testing I wanna know how long this function outputs how long it took
		global $compileTime;
		 $compileTime = \microtime(true)-$executionTime;
		
		return $output;
		
	} // compileTemplate
}