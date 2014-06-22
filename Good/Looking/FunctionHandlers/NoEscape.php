<?php

namespace Good\Looking\FunctionHandlers;

use Good\Looking\FunctionHandler;

class NoEscape implements FunctionHandler
{
    public function __construct()
    {
    }
    
    public function getHandledFunctions()
    {
        return array('noEscape');
    }
    
    public function handleFunction($f, array $args)
    {
        echo $args[0];
        return '';
    }
}

?>