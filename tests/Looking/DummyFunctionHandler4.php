<?php

class DummyFunctionHandler4 implements \Good\Looking\FunctionHandler
{
    function __construct()
    {
    }
    
    function getHandledFunctions()
    {
        return array('b');
    }
    
    function handleFunction($f, \Good\Looking\FunctionHelper $helper, array $args)
    {
        return 'Handler4';
    }
}

?>