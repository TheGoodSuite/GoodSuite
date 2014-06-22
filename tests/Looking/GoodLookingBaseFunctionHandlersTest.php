<?php

class GoodLookingBaseFunctionHandlersTest extends PHPUnit_Framework_TestCase
{
    private $template = '';
    
    public static function setUpBeforeClass()
    {
        // PHPUnit is breaking my tests (but not when run in isolation, only when multiple classes are run)
        // through some of the magic it provides when "trying" to be helpful
        // Let's beark into its blacklist to prevent it from doing this!
        $blacklist = new \PHPUnit_Util_Blacklist();
        $refl = new \ReflectionObject($blacklist);
        $method = $refl->getMethod('initialize');
        $method->setAccessible(true);
        $method->invoke($blacklist);
        $prop = $refl->getProperty('directories');
        $prop->setAccessible(true);
        $arr = $prop->getValue();
        $arr[] = realpath(dirname(__FILE__) . '/../testInputFiles/');
        $prop->setValue($arr);
    }
    
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