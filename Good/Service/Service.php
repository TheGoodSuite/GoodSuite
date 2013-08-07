<?php

namespace Good\Service;

class Service
{
    private $outputDir = null;
    
    public function compile($modifiers, \Good\Rolemodel\Schema $model, $outputDir)
    {
        $this->outputDir = $outputDir;
        
        $compiler = new Compiler($modifiers, $outputDir);
        
        $model->accept($compiler);
    }
    
    public function requireClasses(array $classes)
    {
        foreach ($classes as $class)
        {
            if (\class_exists($class))
            {
                $reflectionClass = new \ReflectionClass($class);
                
                if (!$reflectionClass->isSubClassOf('Base' . ucfirst($class)))
                {
                    // TODO: Turn this into good error handling
                    throw new \Exception('Error: ' . $class . ' does not implement Base' . ucfirst($class) . '. ' .
                          'If you have a class with the name of one of your datatypes, it should ' .
                           'inherit the corresponding base class.');
                }
            }
            else
            {
                // Fix path here
                require $this->outputDir . $class . '.datatype.php';
            }
        }
    }
}

?>