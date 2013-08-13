<?php

namespace Good\Looking;

class Interpreter
{
    // filename of template file
    private $compiledTemplate;
    private $templateVars = array();
    
    private $registeredVars = array();
    private $specialVars = array();
    // special vars in format: $specialVars['varName'] = internalIndex
    // varName = name in template; varLayer = which layer defines the variable;
    // templateVars[internalIndex] = value of specialVar
    
    public function __construct($compiledTemplate, $vars)
    {
        $this->compiledTemplate = $compiledTemplate;
        $this->registeredVars = $vars;
    }
    
    public function interpret()
    {
        require $this->compiledTemplate;
    }
    
    //
    // Functions that should be called by compiled template
    //
    
    private function registerSpecialVar($varName, $internalNumber)
    {
        $this->specialVars[$varName] = $internalNumber;
    }
    
    private function getVar($varName)
    {
        if (isset($this->specialVars[$varName]))
        {
            return $this->templateVars[$this->specialVars[$varName]];
        }
        
        return $this->registeredVars[$varName];
    }
    
    private function arrayItem($array, $item)
    {
        return $array[$item];
    }
}