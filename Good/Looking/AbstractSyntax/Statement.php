<?php

namespace Good\Looking\AbstractSyntax;

class Statement extends ElementWithStatements
{
    private $code;
    
    public function __construct($code)
    {
        parent::__construct();
        
        $this->code = $code;
    }
    
    public function execute(Environment $environment)
    {
        if (\preg_match('/^\s*$/', $this->code) === 1)
        {
            return '';
        }
        
        return 'echo \htmlentities(' . $this->evaluate($this->code) . '); ';
    }
}

?>