<?php

namespace Good\Manners;

class Manners
{
	public function compileStore(\Good\Rolemodel\Schema $model, $outputDir)
	{
		$compiler = new StoreCompiler($outputDir);
		
		$model->accept($compiler);
	}
}

?>