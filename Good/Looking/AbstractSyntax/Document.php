<?php

namespace Good\Looking\AbstractSyntax;

use Good\Looking\Environment;

class Document implements Element
{
    private $statements;
    
    public function __construct($statements)
    {
        $this->statements = $statements;
    }
    
    public function execute(Environment $environment)
    {
        $main = '';
        
        foreach ($this->statements as $statement)
        {
            $main .= $statement->execute($environment);
        }
        
        $out = '<?php ';
        
        foreach ($environment->getUsedFunctionHandlers() as $handler => $file)
        {
            $out .= "if (!class_exists('" . \addslashes($handler) . "', false)) { require '" . \addslashes($file) . "'; };";
            $out .= '$this->functionHandlers["' . \addslashes($handler) . '"] = new ' . $handler . '();';
        }
        
        $out .= $main;
        
        $out .= '?>';
        
        return $out;
    }
}

?>