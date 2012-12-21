<?php

include_once dirname(__FILE__) . '/../Rolemodel/GoodRolemodel.php';
include_once 'SQLStoreCompiler.php';

class GoodMemory
{
	public function compileSQLStore(GoodRolemodelDataModel $model, $outputDir)
	{
		$compiler = new GoodMemorySQLStoreCompiler($outputDir);
		
		$model->accept($compiler);
	}
}

?>