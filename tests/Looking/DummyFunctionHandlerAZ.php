<?php

class DummyFunctionHandlerAZ implements \Good\Looking\FunctionHandler
{
    function __construct()
    {
    }
    
    private $var = 0;
    
    function getHandledFunctions()
    {
        return array('a', 'z');
    }
    
    function handleFunction($f, array $args)
    {
        if ($f == 'a')
        {
            return 'a';
        }
        else if ($f == 'z')
        {
            return 'z';
        }
    }
}

?>