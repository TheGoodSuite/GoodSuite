<?php

namespace Good\Looking;

class Looking
{
    private $templateFileName;
    private $registeredVars = array();
    private $grammar;
    
    private $functionHandlers = array();
    private $functions = array();
    
    public function __construct($fileName)
    {
        $this->templateFileName = $fileName;
        $this->grammar = new Grammar();
    }
    
    public function registerVar($varName, $varValue)
    {
        if (\preg_match('/'. $this->grammar->varName .'/', $varName) === 1)
        // if the varName matches variable syntax (and does not match a keyword)
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
    
    public function registerFunctionHandler($handler)
    {
        $reflection = new \ReflectionClass($handler);
        
        if (!$reflection->implementsInterface('\\Good\\Looking\\FunctionHandler'))
        {
            throw new \Exception("Given class does not implement \\Good\\Looking\\FunctionHandler");
        }
        
        $this->functionHandlers[$handler] = $reflection->getFilename();
        
        $instance = new $handler();
        
        foreach ($instance->getHandledFunctions() as $function)
        {
            if (array_key_exists($function, $this->functions))
            {
                throw new \Exception("Two FunctionHandlers that handle the same function (" . $function . ") registered");
            }
            
            $this->functions[$function] = $handler;
        }
    }
    
    public function display()
    {
        if (!\file_exists($this->templateFileName))
        {
            throw new \Exception('Template not found.');
        }
        
        $this->compileIfNecessary($this->templateFileName);
        
        $interpreter = new Interpreter($this->registeredVars);
        
        $interpreter->interpret($this->templateFileName . '.compiledTemplate',
                                new FunctionHelper($this, $interpreter, $this->templateFileName));
    }
    
    public function compileIfNecessary($template)
    {
        if (!\file_exists($template . '.compiledTemplate') ||
                    \filemtime($template) > \filemtime($template . '.compiledTemplate'))
        {
            $environment = new Environment($this->functionHandlers, $this->functions);
            
            $compiler = new Compiler($this->grammar, $environment);
            $compiler->compile($template, $template . '.compiledTemplate');
        }
    }
}