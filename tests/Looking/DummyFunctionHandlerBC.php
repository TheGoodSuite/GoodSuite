<?php

namespace ns;

class DummyFunctionHandlerBC implements \Good\Looking\FunctionHandler
{
    function __construct()
    {
    }

    private $var = 0;

    function getHandledFunctions()
    {
        return array('b', 'c');
    }

    function handleFunction($f, \Good\Looking\FunctionHelper $helper, array $args)
    {
        if ($f == 'b')
        {
            return 'b';
        }
        else if ($f == 'c')
        {
            return 'c';
        }
    }
}

?>
