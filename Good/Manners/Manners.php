<?php

namespace Good\Manners;

class Manners
{
	public function compileStore(\Good\Rolemodel\DataModel $model, $outputDir)
	{
		$compiler = new StoreCompiler($outputDir);
		
		$model->accept($compiler);
	}
}

?>