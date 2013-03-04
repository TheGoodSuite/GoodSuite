<?php

namespace Good\Manners;

include_once dirname(__FILE__) . '/../Rolemodel/Rolemodel.php';
include_once 'StoreCompiler.php';

class Manners
{
	public function compileStore(\Good\Rolemodel\DataModel $model, $outputDir)
	{
		$compiler = new StoreCompiler($outputDir);
		
		$model->accept($compiler);
	}
}

?>