<?php

namespace Good\Looking;

Class Compiler
{
    private $grammar;
    private $environment;
    
    public function __construct(Grammar $grammar, Environment $environment)
    {
        $this->grammar = $grammar;
        $this->environment = $environment;
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
        
        $output = $document->execute($this->environment);
        
        return $output;
    }
}