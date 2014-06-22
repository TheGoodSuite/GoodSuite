<?php

namespace Good\Looking\FunctionHandlers;

use Good\Looking\FunctionHandler;
use Good\Looking\FunctionHelper;

class NoEscape implements FunctionHandler
{
    public function __construct()
    {
    }
    
    public function getHandledFunctions()
    {
        return array('noEscape');
    }
    
    public function handleFunction($function, FunctionHelper $helper, array $arguments)
    {
        echo $arguments[0];
        return '';
    }
}

?>