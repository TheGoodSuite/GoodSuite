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
    
    function handleFunction($f, array $args)
    {
        return 'Handler4';
    }
}

?>