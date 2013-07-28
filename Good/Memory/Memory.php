<?php

namespace Good\Memory;

class Memory
{
	public function compileSQLStore(\Good\Rolemodel\Schema $model, $outputDir)
	{
		$compiler = new SQLStoreCompiler($outputDir);
		
		$model->accept($compiler);
	}
}

?>