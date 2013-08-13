<?php

namespace Good\Looking;

class Looking
{
    private $templateFileName;
    private $registeredVars = array();
    
    public function __construct($fileName)
    {
        $this->templateFileName = $fileName;
    }
    
    public function registerVar($varName, $varValue)
    {
        if (\preg_match('/'. Regexes::$varName .'/', $varName) > 0 &&
             \preg_match('/'. Regexes::$controlStructure .'/', $varName) == 0)
        // if the varName matches variable syntax and does not match a control structure
        {
            $this->registeredVars[$varName] = $varValue;
            
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function registerMultipleVars($array)
    {
        foreach ($array as $key => $value)
        {
            $this->registerVar($key, $value);
        }
    }
    
    public function display()
    {
        if (!\file_exists($this->templateFileName))
        {
            throw new \Exception('Template not found.');
        }
        
        if (!\file_exists($this->templateFileName . '.compiledTemplate') ||
                    \filemtime($this->templateFileName) > \filemtime($this->templateFileName . '.compiledTemplate'))
        {
            $compiler = new Compiler();
            $compiler->compile($this->templateFileName, $this->templateFileName . '.compiledTemplate');
        }
        
        $interpreter = new Interpreter($this->templateFileName . '.compiledTemplate', 
                                       $this->registeredVars);
        
        $interpreter->interpret();
    }
}