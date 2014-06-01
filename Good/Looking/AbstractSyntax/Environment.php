<?php

namespace Good\Looking\AbstractSyntax;

class Environment
{
    private $hiddenVars = 0;
    
    public function getNewHiddenVar()
    {
        $this->hiddenVars++;
        
        return $this->hiddenVars - 1;
    }
}

?>