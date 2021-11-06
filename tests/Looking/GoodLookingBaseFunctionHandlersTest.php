<?php

class GoodLookingBaseFunctionHandlersTest extends \PHPUnit\Framework\TestCase
{
    private $template = '';

    public function setUp(): void
    {
        $this->template = dirname(__FILE__) . '/../testInputFiles/template';
        file_put_contents($this->template, '');
    }

    public function tearDown(): void
    {
        unlink($this->template);
        unlink($this->template . '.compiledTemplate');
    }

    public function testNoEscape()
    {
        $this->expectOutputString('<br>');

        file_put_contents($this->template, '<: noEscape("<br>") :>');

        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerFunctionHandler('\\Good\Looking\FunctionHandlers\\NoEscape');
        $goodLooking->display();
    }

    public function testInclude()
    {
        $this->expectOutputString('aBa');

        file_put_contents($this->template, '<: "a"; include("b"); "a" :>');
        file_put_contents(dirname($this->template) . '/b', 'B');

        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerFunctionHandler('\\Good\Looking\FunctionHandlers\\IncludeHandler');
        $goodLooking->display();

        unlink(dirname($this->template) . '/b');
        unlink(dirname($this->template) . '/b.compiledTemplate');
    }

    public function testIncludeWithVariables()
    {
        $this->expectOutputString('1728');

        file_put_contents($this->template, '<: $a = 1; $a; $b; include("b") :>');
        file_put_contents(dirname($this->template) . '/b', '<: $a + 1; $b + 1 :>');

        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerFunctionHandler('\\Good\Looking\FunctionHandlers\\IncludeHandler');
        $goodLooking->registerVar('b', 7);
        $goodLooking->display();

        unlink(dirname($this->template) . '/b');
        unlink(dirname($this->template) . '/b.compiledTemplate');
    }

    public function testIncludeRelativePath()
    {
        $this->expectOutputString('abc');

        file_put_contents($this->template, '<: "a"; include("inc/b") :>');
        mkdir(dirname($this->template) . '/inc');
        file_put_contents(dirname($this->template) . '/inc/b', '<: "b"; include("../c") :>');
        file_put_contents(dirname($this->template) . '/c', '<: "c" :>');

        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerFunctionHandler('\\Good\Looking\FunctionHandlers\\IncludeHandler');
        $goodLooking->display();

        unlink(dirname($this->template) . '/inc/b');
        unlink(dirname($this->template) . '/inc/b.compiledTemplate');
        rmdir(dirname($this->template) . '/inc/');
        unlink(dirname($this->template) . '/c');
        unlink(dirname($this->template) . '/c.compiledTemplate');
    }

    public function testIncludeSeparateContexts()
    {
        $this->expectOutputString('a1:123.a2:123.a3:123.');

        file_put_contents($this->template, '<: forrange (1 --> 3 as $i): "a"; $i; ":"; include("b"); "."; endforrange; :>');
        file_put_contents(dirname($this->template) . '/b', '<: forrange (1 --> 3 as $i): $i; endforrange; :>');

        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerFunctionHandler('\\Good\Looking\FunctionHandlers\\IncludeHandler');
        $goodLooking->registerVar('b', 7);
        $goodLooking->display();

        unlink(dirname($this->template) . '/b');
        unlink(dirname($this->template) . '/b.compiledTemplate');
    }
}

?>
