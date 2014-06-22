<?php

namespace Good\Looking;

class Interpreter
{
    private $automaticVars = array();
    private $automaticVarsStack = array();
    private $freedAutomaticVars = array();
    private $freedAutomaticVarsStack = array();
    private $hiddenVars = array();
    private $hiddenVarsStack = array();
    private $templateVars = array();
    private $userVars = array();
    private $functionHandlers = array();
    private $functionHelper;
    
    public function __construct($vars)
    {
        $this->templateVars = $vars;
    }
    
    public function interpret($compiledTemplate, FunctionHelper $functionHelper)
    {
        $this->functionHelper = $functionHelper;
        require $compiledTemplate;
    }
    
    //
    // Functions that should be called by compiled template
    //
    
    private function getVar($varName)
    {
        if (isset($this->templateVars[$varName]))
        {
            return $this->templateVars[$varName];
        }
        else if (isset($this->automaticVars[$varName]))
        {
            return $this->automaticVars[$varName];
        }
        else if (isset($this->userVars[$varName]))
        {
            return $this->userVars[$varName];
        }
        else
        {
            throw new \Exception("Template uses unknown variable");
        }
    }
    
    private function setVar($varName, $value)
    {
        if (isset($this->templateVars[$varName]))
        {
            throw new \Exception("Can't override template variable");
        }
        else if (isset($this->automaticVars[$varName]))
        {
            throw new \Exception("Can't override automatic variable");
        }
        else
        {
            $this->userVars[$varName] = $value;
            return '';
        }
    }
    
    private function setVars($vars, $value)
    {
        foreach ($vars as $var)
        {
            $this->setVar($var, $value);
        }
    }
    
    private function checkVarName($varName)
    {
        if (isset($this->templateVars[$varName]))
        {
            throw new \Exception("Can't use template variable for loop");
        }
        else if (isset($this->automaticVars[$varName]))
        {
            // allow reuse of automatic variables *after* loop
            if (!isset($this->freedAutomaticVars[$varName]) || !$this->freedAutomaticVars[$varName])
            {
                throw new \Exception("Can't re-use automatic variable until first loop is done");
            }
        }
        else if (isset($this->userVars[$varName]))
        {
            throw new \Exception("Can't use existing user variable for loop");
        }
    }
    
    private function unsetVar($varName)
    {
        // only applies to automatic vars
        unset($this->automaticVars[$varName]);
    }
    
    private function arrayItem($array, $item)
    {
        return $array[$item];
    }
    
    //
    // Functions that should be called by a function helper
    //
    
    public function pushContext()
    {
        $this->hiddenVarsStack[] = $this->hiddenVars;
        $this->automaticVarsStack[] = $this->automaticVars;
        $this->freedAutomaticVarsStack[] = $this->freedAutomaticVars;
        $this->hiddenVars = array();
        $this->automaticVars = array();
        $this->freedAutomaticVars = array();
    }
    
    public function popContext()
    {
        $this->hiddenVars = array_pop($this->hiddenVarsStack);
        $this->automaticVars = array_pop($this->automaticVarsStack);
        $this->freedAutomaticVars = array_pop($this->freedAutomaticVarsStack);
    }
}