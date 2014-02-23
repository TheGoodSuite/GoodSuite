<?php

namespace Good\Looking\AbstractSyntax;

use Good\Looking\Grammar;

class ForrangeStructure extends ElementWithStatements
{
    private $from;
    private $to;
    private $statements;
    private $counter;
    
    public function __construct($grammar, $from, $to, $counter, $statements)
    {
        parent::__construct($grammar);
        
        $this->statements = $statements;
        $this->from = $from;
        $this->to = $to;
        $this->counter = $counter;
    }
    
    public function execute(Environment $environment)
    {
        $counter = $environment->getTemplateVar();
        $from = $environment->getTemplateVar();
        $to = $environment->getTemplateVar();
        $delta = $environment->getTemplateVar();
        
        $out  = '$this->templateVars[' . $from . '] = intval(' . $this->evaluate($this->from) . '); ';
        $out .= '$this->templateVars[' . $to . '] = intval(' . $this->evaluate($this->to) . '); ';
        $out .= '$this->templateVars[' . $delta . '] = $this->templateVars[' . $from . '] < ' .
                    '$this->templateVars[' . $to . '] ? 1 : -1; ';
        
        if ($this->counter != null)
        {
            $out .= '$this->registerSpecialVar("' . $this->counter . '", ' . $counter . ');';
        }
        
        $out .= 'for ($this->templateVars[' . $counter . '] = $this->templateVars[' . $from . ']; ' . 
                    '$this->templateVars[' . $counter . '] * $this->templateVars[' . $delta . '] <= ' .
                      '$this->templateVars[' . $to . '] * $this->templateVars[' . $delta . ']; ' .
                       '$this->templateVars[' . $counter . '] += $this->templateVars[' . $delta . ']):';
        
        foreach ($this->statements as $statement)
        {
            $out .= $statement->execute($environment);
        }
        
        $out .= 'endfor; ';
        $out .= '$this->templateVars[' . $counter . '] -= $this->templateVars[' . $from . '];';
        
        return $out;
    }
}

?>