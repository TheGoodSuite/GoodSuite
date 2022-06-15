<?php

namespace Good\Service;

use Good\Rolemodel\Rolemodel;

class Service
{
    private $outputDir = null;

    public function compile($modifiers, \Good\Rolemodel\Schema $model, $outputDir)
    {
        $this->outputDir = $outputDir;

        $compiler = new Compiler($modifiers, $outputDir);

        $compiler->compile($model);
    }

    public function autocompile($inputDir, $outputDir, $modifiers)
    {
        $inputTime = $this->lastModifiedRecursively($inputDir, 'datatype');
        $outputTime = $this->lastModifiedRecursively($outputDir, 'datatype');

        $files = $this->listFiles($inputDir, 'datatype');
        $rolemodel = new Rolemodel();
        $schema = $rolemodel->createSchema($files);

        if ($outputTime === null || $outputTime < $inputTime)
        {
            $this->compile($modifiers, $schema, $outputDir);
        }

        foreach ($schema->getTypeDefitions() as $type)
        {
            require_once $outputDir . $type->getName() . '.datatype.php';
            require_once $outputDir . $type->getName() . 'Resolver.php';
            require_once $outputDir . $type->getName() . 'Condition.php';
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
