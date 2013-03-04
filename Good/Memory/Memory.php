<?php

namespace Good\Memory;

include_once dirname(__FILE__) . '/../Rolemodel/Rolemodel.php';
include_once 'SQLStoreCompiler.php';

class Memory
{
	public function compileSQLStore(\Good\Rolemodel\DataModel $model, $outputDir)
	{
		$compiler = new SQLStoreCompiler($outputDir);
		
		$model->accept($compiler);
	}
}

?>