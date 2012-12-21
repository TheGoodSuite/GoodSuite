<?php

include_once dirname(__FILE__) . '/../Rolemodel/GoodRolemodel.php';
include_once 'StoreCompiler.php';

class GoodManners
{
	public function compileStore(GoodRolemodelDataModel $model, $outputDir)
	{
		$compiler = new GoodMannersStoreCompiler($outputDir);
		
		$model->accept($compiler);
	}
}

?>