<?php

namespace ns;

class DummyFunctionHandlerFG implements \Good\Looking\FunctionHandler
{
    function __construct()
    {
    }
    
    function getHandledFunctions()
    {
        return array('f', 'g');
    }
    
    function handleFunction($f, array $args)
    {
        if ($f == 'f')
        {
            return 'f';
        }
        else if ($f == 'g')
        {
            return 'g';
        }
    }
}

?>