<?php

class DummyFunctionHandler1 implements \Good\Looking\FunctionHandler
{
    function __construct()
    {
    }
    
    function getHandledFunctions()
    {
        return array('a', 'b');
    }
    
    function handleFunction($f, \Good\Looking\FunctionHelper $helper, array $args)
    {
        if ($f == 'a')
        {
            return "A";
        }
        else if ($f == 'b')
        {
            return "B";
        }
    }
}

?>