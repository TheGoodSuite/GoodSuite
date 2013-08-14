<?php

namespace Good\Looking;

Class Compiler
{
    private $grammar;
    
    public function __construct(Grammar $grammar)
    {
        $this->grammar = $grammar;
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
        $factory = new AbstractSyntax\Factory($this->grammar);
        $parser = new Parser($this->grammar, $factory);
        
        $document = $parser->parseDocument($input);
        
        $environment = new AbstractSyntax\Environment();
        
        $output = $document->execute($environment);
        
        return $output;
    }
}