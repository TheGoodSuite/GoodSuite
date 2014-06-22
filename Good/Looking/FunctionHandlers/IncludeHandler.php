<?php

namespace Good\Looking\FunctionHandlers;

use Good\Looking\FunctionHandler;
use Good\Looking\FunctionHelper;

class IncludeHandler implements FunctionHandler
{
    public function __construct()
    {
    }
    
    public function getHandledFunctions()
    {
        return array('include');
    }
    
    public function handleFunction($function, FunctionHelper $helper, array $arguments)
    {
        $oldFile = $helper->getTemplatePath();
        $file = dirname($oldFile) . '/' . $arguments[0];
        
        $helper->compileIfModified($file);
        
        $helper->pushContext();
        $helper->setTemplatePath($file);
        
        $helper->interpret($file . '.compiledTemplate');
        
        $helper->setTemplatePath($oldFile);
        $helper->popContext();
        
        return '';
    }
}

?>