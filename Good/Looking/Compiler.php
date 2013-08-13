<?php

namespace Good\Looking;

Class Compiler
{
    public function __construct()
    {
    }
    
    public function compile($input, $output)
    {
        $compiledTemplate = $this->compileTemplate(file_get_contents($input));
        
        $file = \fopen($output, 'w+');
        \fwrite($file, $compiledTemplate);
        \fclose($file);
        
    }
    
    private function compileTemplate($input)
    {
        $factory = new AbstractSyntax\Factory();
        $parser = new Parser($factory);
        
        $document = $parser->parseDocument($input);
        
        $environment = new AbstractSyntax\Environment();
        
        $output = $document->execute($environment);
        
        return $output;
    }
}