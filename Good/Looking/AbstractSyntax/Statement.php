<?php

namespace Good\Looking\AbstractSyntax;

use Good\Looking\Environment;

class Statement extends ElementWithStatements
{
    private $code;
    
    public function __construct($grammar, $code)
    {
        parent::__construct($grammar);
        
        $this->code = $code;
    }
    
    public function execute(Environment $environment)
    {
        if (\preg_match('/^\s*$/', $this->code) === 1)
        {
            return '';
        }
        
        return 'echo \htmlentities(' . $this->evaluate($this->code, $environment) . '); ';
    }
}

?>