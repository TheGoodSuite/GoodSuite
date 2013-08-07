<?php

namespace Good\Looking;

Class Compiler
{
    public function __construct()
    {
    } // __construct
    
    public function compile($input, $output)
    {
        $compiledTemplate = $this->compileTemplate(file_get_contents($input));
        
        $file = \fopen($output, 'w+');
        \fwrite($file, $compiledTemplate);
        \fclose($file);
        
    } // compile
    
    private function compileTemplate($input)
    {
        // for testing I really want to know how long this function takes to be executed
        $executionTime = \microtime(true);
        
        $factory = new AbstractSyntax\Factory();
        $parser = new Parser($factory);
        
        $document = $parser->parseDocument($input);
        
        $environment = new AbstractSyntax\Environment();
        
        $output = $document->execute($environment);
        
        // for testing I wanna know how long this function outputs how long it took
        global $compileTime;
         $compileTime = \microtime(true)-$executionTime;
        
        return $output;
        
    } // compileTemplate
}