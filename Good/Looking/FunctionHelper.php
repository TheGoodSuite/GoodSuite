<?php

namespace Good\Looking;

class FunctionHelper
{
    private $looking;
    private $interpreter;
    private $templatePath;
    
    public function __construct(Looking $looking, Interpreter $interpreter, $templatePath)
    {
        $this->looking = $looking;
        $this->interpreter = $interpreter;
        $this->templatePath = $templatePath;
    }
    
    public function compileIfModified($file)
    {
        $this->looking->compileIfnecessary($file);
    }
    
    public function interpret($file)
    {
        $this->interpreter->interpret($file, $this);
    }
    
    public function pushContext()
    {
        $this->interpreter->pushContext();
    }
    
    public function popContext()
    {
        $this->interpreter->popContext();
    }
    
    public function getTemplatePath()
    {
        return $this->templatePath;
    }
    
    public function setTemplatePath($templatePath)
    {
        $this->templatePath = $templatePath;
    }
}

?>