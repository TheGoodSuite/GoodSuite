<?php

class GoodLookingBaseFunctionHandlersTest extends PHPUnit_Framework_TestCase
{
    private $template = '';
    
    public function setUp()
    {
        $this->template = dirname(__FILE__) . '/../testInputFiles/template';
        file_put_contents($this->template, '');
    }
    
    public function tearDown()
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
}

?>