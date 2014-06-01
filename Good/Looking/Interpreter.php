<?php

namespace Good\Looking;

class Interpreter
{
    // filename of template file
    private $compiledTemplate;
    
    private $automaticVars = array();
    private $hiddenVars = array();
    private $templateVars = array();
    private $userVars = array();
    
    public function __construct($compiledTemplate, $vars)
    {
        $this->compiledTemplate = $compiledTemplate;
        $this->templateVars = $vars;
    }
    
    public function interpret()
    {
        require $this->compiledTemplate;
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
            throw new \Exception("Can't use existing automatic variable for loop");
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
}