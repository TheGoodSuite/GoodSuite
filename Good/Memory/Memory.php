<?php

namespace Good\Memory;

class Memory
{
	public function compileSQLStore(\Good\Rolemodel\DataModel $model, $outputDir)
	{
		$compiler = new SQLStoreCompiler($outputDir);
		
		$model->accept($compiler);
	}
}

?>