<?php

namespace Good\Looking\AbstractSyntax;

use Good\Looking\Grammar;
use Good\Looking\Environment;

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
        $from = $environment->getNewHiddenVar();
        $to = $environment->getNewHiddenVar();
        $delta = $environment->getNewHiddenVar();
        
        $out = '';
        
        if ($this->counter != null)
        {
            $out .= '$this->checkVarName("' . $this->counter . '");';
            $counter = '$this->automaticVars["' . $this->counter . '"]';
        }
        else
        {
            $counter = '$this->hiddenVars[' . $environment->getNewHiddenVar() . ']';
        }
        
        $out .= '$this->hiddenVars[' . $from . '] = intval(' . $this->evaluate($this->from, $environment) . '); ';
        $out .= '$this->hiddenVars[' . $to . '] = intval(' . $this->evaluate($this->to, $environment) . '); ';
        $out .= '$this->hiddenVars[' . $delta . '] = $this->hiddenVars[' . $from . '] < ' .
                    '$this->hiddenVars[' . $to . '] ? 1 : -1; ';
        
        $out .= 'for (' . $counter . ' = $this->hiddenVars[' . $from . ']; ' . 
                    $counter . ' * $this->hiddenVars[' . $delta . '] <= ' .
                      '$this->hiddenVars[' . $to . '] * $this->hiddenVars[' . $delta . ']; ' .
                       $counter . ' += $this->hiddenVars[' . $delta . ']):';
        
        foreach ($this->statements as $statement)
        {
            $out .= $statement->execute($environment);
        }
        
        $out .= 'endfor;';
        
        if ($this->counter != null)
        {
            
            $out .= '$this->freedAutomaticVars["' . $this->counter . '"] = true;';
        }
        
        return $out;
    }
}

?>