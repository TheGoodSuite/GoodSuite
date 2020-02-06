<?php

class DummyFunctionHandler6 implements \Good\Looking\FunctionHandler
{
    function __construct()
    {
    }

    private $var = 0;

    function getHandledFunctions()
    {
        return array('set', 'get');
    }

    function handleFunction($f, \Good\Looking\FunctionHelper $helper, array $args)
    {
        if ($f == 'set')
        {
            $this->var = $args[0];
            return '';
        }
        else if ($f == 'get')
        {
            return $this->var;
        }
    }
}

?>
