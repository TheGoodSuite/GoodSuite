<?php

use Good\Service\Service;

/**
 * @runTestsInSeparateProcesses
 */
class GoodServiceJITTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown(): void
    {
        $path = __dir__ . '/../generated/';
        $iterator = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
        $recursiveIterator = new \RecursiveIteratorIterator($iterator);

        foreach ($recursiveIterator as $file)
        {
            if ($file->getExtension() === 'datatype' ||
                $file->getExtension() === 'php')
            {
                unlink($file->getPathname());
            }
        }
    }

    public function testUsingCompilingWithJIT()
    {
        \touch(__dir__ . '/../generated/MyType.datatype.php');
        \sleep(1);
        \file_put_contents(__dir__ . '/../generated/MyType.datatype', 'datatype MyType { int n; }');

        $service = new \Good\Service\Service([
            "modifiers" => [],
            "inputDir" => __dir__ . '/../generated/',
            "outputDir" => __dir__ . '/../generated/'
        ]);

        $service->load();

        $this->assertTrue(\class_exists('\\MyType'));
    }

    public function testNotOverusingJIT()
    {
        \file_put_contents(__dir__ . '/../generated/MyType.datatype', 'datatype MyType { int n; }');
        \sleep(1);
        \touch(__dir__ . '/../generated/MyType.datatype.php');

        $service = new \Good\Service\Service([
            "modifiers" => [],
            "inputDir" => __dir__ . '/../generated/',
            "outputDir" => __dir__ . '/../generated/'
        ]);

        $service->load();

        $this->assertFalse(\class_exists('\\MyType'));
    }
}

?>
