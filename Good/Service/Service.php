<?php

namespace Good\Service;

class Service
{
    private $outputDir = null;
    
    public function compile($modifiers, \Good\Rolemodel\Schema $model, $outputDir)
    {
        $this->outputDir = $outputDir;
        
        $compiler = new Compiler($modifiers, $outputDir);
        
        $model->acceptSchemaVisitor($compiler);
    }
}

?>