<?php

include_once dirname(__FILE__) . '/../Rolemodel/GoodRolemodel.php';
include_once 'Compiler.php';

class GoodService
{
	public function compile($modifiers, GoodRolemodelDataModel $model, $outputDir)
	{
		$compiler = new GoodServiceCompiler($modifiers, $outputDir);
		
		$model->accept($compiler);
	}
	
	public function requireClasses(array $classes)
	{
		foreach ($classes as $class)
		{
			if (class_exists($class))
			{
				$reflectionClass = new ReflectionClass($class);
				
				if (!$reflectionClass->isSubClassOf('Base' . ucfirst($class)))
				{
					// TODO: Turn this into good error handling
					die('Error: ' . $class . ' does not implement Base' . ucfirst($class) . '. ' .
					      'If you have a class with the name of one of your datatypes, it should ' .
						   'inherit the corresponding base class.');
				}
			}
			else
			{
				// Fix path here
				require 'compiled/' . $class . '.datatype.php';
			}
		}
	}
}

?>