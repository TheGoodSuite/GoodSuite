<?php

class DummyFunctionHandlerDE implements \Good\Looking\FunctionHandler
{
    function __construct()
    {
    }
    
    private $var = 0;
    
    function getHandledFunctions()
    {
        return array('d', 'e');
    }
    
    function handleFunction($f, \Good\Looking\FunctionHelper $helper, array $args)
    {
        if ($f == 'd')
        {
            return 'd';
        }
        else if ($f == 'e')
        {
            return 'e';
        }
    }
}

?>