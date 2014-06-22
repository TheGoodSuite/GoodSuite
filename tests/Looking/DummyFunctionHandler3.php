<?php

class DummyFunctionHandler3 implements \Good\Looking\FunctionHandler
{
    function __construct()
    {
    }
    
    function getHandledFunctions()
    {
        return array('a');
    }
    
    function handleFunction($f, \Good\Looking\FunctionHelper $helper, array $args)
    {
        return 'Handler3';
    }
}

?>