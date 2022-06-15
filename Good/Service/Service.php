<?php

namespace Good\Service;

use Good\Rolemodel\Rolemodel;

class Service
{
    private $inputDir;
    private $outputDir;
    private $modifiers;

    public function __construct($config)
    {
        $this->inputDir = $config['inputDir'];
        $this->outputDir = $config['outputDir'];
        $this->modifiers = array_key_exists('modifiers', $config) ? $config['modifiers'] : [];
    }

    public static function compile($modifiers, \Good\Rolemodel\Schema $model, $outputDir)
    {
        $compiler = new Compiler($modifiers, $outputDir);

        $compiler->compile($model);
    }

    public function load()
    {
        $inputTime = $this->lastModifiedRecursively($this->inputDir, 'datatype');
        $outputTime = $this->lastModifiedRecursively($this->outputDir, 'datatype');

        $files = $this->listFiles($this->inputDir, 'datatype');
        $rolemodel = new Rolemodel();
        $schema = $rolemodel->createSchema($files);

        if ($outputTime === null || $outputTime < $inputTime)
        {
            $this->compile($this->modifiers, $schema, $this->outputDir);
        }

        foreach ($schema->getTypeDefitions() as $type)
        {
            require_once $this->outputDir . $type->getName() . '.datatype.php';
        }
    }

    private function lastModifiedRecursively($path, $extension)
    {
        $lastModified = null;

        $iterator = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
        $recursiveIterator = new \RecursiveIteratorIterator($iterator);

        foreach ($recursiveIterator as $file)
        {
            if ($file->getExtension() === $extension &&
                ($lastModified === null || $lastModified < $file->getMTime()))
            {
                $lastModified = $file->getMTime();
            }
        }

        return $lastModified;
    }

    private function listFiles($path, $extension)
    {
        $files = [];

        $iterator = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
        $recursiveIterator = new \RecursiveIteratorIterator($iterator);

        foreach ($recursiveIterator as $file)
        {
            if ($file->getExtension() === $extension)
            {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}

?>
