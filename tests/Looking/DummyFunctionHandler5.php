<?php

class DummyFunctionHandler5 implements \Good\Looking\FunctionHandler
{
    function __construct()
    {
    }
    
    function getHandledFunctions()
    {
        return array('add');
    }
    
    function handleFunction($f, \Good\Looking\FunctionHelper $helper, array $args)
    {
        if ($f == 'add')
        {
            return $args[0] + $args[1];
        }
    }
}

?>