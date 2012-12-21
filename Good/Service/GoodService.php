<?php

// TODO: fix include (currently dependent on location of current script)
include_once '../../Rolemodel/GoodRolemodel.php';
include_once 'Compiler.php';

class GoodService
{
	public function compile($modifiers, GoodRolemodelDataModel $model, $outputDir)
	{
		$compiler = new GoodServiceCompiler($modifiers, $outputDir);
		
		$model->accept($compiler);
		$compiler->finish();
	}
}

?>