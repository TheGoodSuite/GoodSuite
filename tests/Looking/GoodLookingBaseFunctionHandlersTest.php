<?php

use Good\Looking\Looking;

class GoodLookingBaseFunctionHandlersTest extends \PHPUnit\Framework\TestCase
{
    private $baseInputFilesDir = __dir__ . '/../testInputFiles/GoodLooking/GoodLookingBaseFunctionHandlersTest/';

    public function tearDown(): void
    {
        $path = $this->baseInputFilesDir;
        $iterator = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
        $recursiveIterator = new \RecursiveIteratorIterator($iterator);

        foreach ($recursiveIterator as $file)
        {
            if ($file->getExtension() === 'compiledTemplate')
            {
                unlink($file->getPathname());
            }
        }
    }

    public function testNoEscape()
    {
        $this->expectOutputString('<br>');

        $goodLooking = new Looking($this->baseInputFilesDir . 'noEscape.template');
        $goodLooking->registerFunctionHandler('\\Good\Looking\FunctionHandlers\\NoEscape');
        $goodLooking->display();
    }

    public function testInclude()
    {
        $this->expectOutputString('aBa');

        $goodLooking = new Looking($this->baseInputFilesDir . 'include.template');
        $goodLooking->registerFunctionHandler('\\Good\Looking\FunctionHandlers\\IncludeHandler');
        $goodLooking->display();
    }

    public function testIncludeWithVariables()
    {
        $this->expectOutputString('1728');

        $goodLooking = new Looking($this->baseInputFilesDir . 'includeWithVariables.template');
        $goodLooking->registerFunctionHandler('\\Good\Looking\FunctionHandlers\\IncludeHandler');
        $goodLooking->registerVar('b', 7);
        $goodLooking->display();
    }

    public function testIncludeRelativePath()
    {
        $this->expectOutputString('abc');

        $goodLooking = new Looking($this->baseInputFilesDir . 'includeRelativePath.template');
        $goodLooking->registerFunctionHandler('\\Good\Looking\FunctionHandlers\\IncludeHandler');
        $goodLooking->display();
    }

    public function testIncludeSeparateContexts()
    {
        $this->expectOutputString('a1:123.a2:123.a3:123.');

        $goodLooking = new Looking($this->baseInputFilesDir . 'includeSeparateContexts.template');
        $goodLooking->registerFunctionHandler('\\Good\Looking\FunctionHandlers\\IncludeHandler');
        $goodLooking->display();
    }
}

?>
