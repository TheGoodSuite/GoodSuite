<?php

namespace Good\Looking;

class Environment
{
    private $hiddenVars = 0;
    
    private $functionHandlers;
    private $functions;
    private $functionHandlersUsed;
    
    public function __construct($functionHandlers, $functions)
    {
        $this->functionHandlers = $functionHandlers;
        $this->functions = $functions;
        
        $this->functionHandlersUsed = array();
    }
    
    public function getNewHiddenVar()
    {
        $this->hiddenVars++;
        
        return $this->hiddenVars - 1;
    }
    
    public function getFunctionHandlerForFunction($function)
    {
        $handler = $this->functions[$function];
        
        $this->functionHandlersUsed[$handler] = $this->functionHandlers[$handler];
        
        return $handler;
    }
    
    public function getUsedFunctionHandlers()
    {
        return $this->functionHandlersUsed;
    }
}

?>