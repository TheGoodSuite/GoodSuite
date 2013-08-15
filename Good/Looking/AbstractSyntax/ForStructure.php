<?php

namespace Good\Looking\AbstractSyntax;

use Good\Looking\Grammar;

class ForStructure extends ElementWithStatements
{
    private $from;
    private $to;
    private $statements;
    
    public function __construct($grammar, $from, $to, $statements)
    {
        parent::__construct($grammar);
        
        $this->statements = $statements;
        $this->from = $from;
        $this->to = $to;
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
        $out .= 'for ($this->templateVars[' . $counter . '] = $this->templateVars[' . $from . ']; ' . 
                    '$this->templateVars[' . $counter . '] * $this->templateVars[' . $delta . '] <= ' .
                      '$this->templateVars[' . $to . '] * $this->templateVars[' . $delta . ']; ' .
                       '$this->templateVars[' . $counter . '] += $this->templateVars[' . $delta . ']):';
        
        foreach ($this->statements as $statement)
        {
            $out .= $statement->execute($environment);
        }
        
        $out .= 'endfor; ';
        
        return $out;
    }
}

?>