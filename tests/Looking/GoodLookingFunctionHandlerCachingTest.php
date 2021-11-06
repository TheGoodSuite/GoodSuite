<?php

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class GoodLookingFunctionHandlerCachingTest extends \PHPUnit\Framework\TestCase
{
    public static function _tearDownAfterClass()
    {
        unlink(dirname(__FILE__) . '/../testInputFiles/template');
        unlink(dirname(__FILE__) . '/../testInputFiles/template.compiledTemplate');
    }

    public function testCompileTemplate()
    {
        // not really a test, but set up
        // however, it needed to run in a separate process and making it a test
        // was the easiest way to make that happen.
        require 'DummyFunctionHandlerAZ.php';
        require 'DummyFunctionHandlerBC.php';
        require 'DummyFunctionHandlerDE.php';
        require 'DummyFunctionHandlerFG.php';

        $this->expectOutputString('abc');

        file_put_contents(dirname(__FILE__) . '/../testInputFiles/template', '<: a(); b(); c() :>');

        $goodLooking = new \Good\Looking\Looking(dirname(__FILE__) . '/../testInputFiles/template');
        $goodLooking->registerFunctionHandler('DummyFunctionHandlerAZ');
        $goodLooking->registerFunctionHandler('DummyFunctionHandlerDE');
        $goodLooking->registerFunctionHandler('\\ns\\DummyFunctionHandlerFG');
        $goodLooking->registerFunctionHandler('\\ns\\DummyFunctionHandlerBC');
        $goodLooking->display();
    }

    public function testInterpretTemplate()
    {
        $this->expectOutputString('abc');

        $goodLooking = new \Good\Looking\Looking(dirname(__FILE__) . '/../testInputFiles/template');
        // Not showing registering any functions.
        // This works because I know the template is already compiledTemplate
        // and it is necessary because of the specific things I'm testing here
        // In the future, there should be a system that lets you only register
        // function handlers on compile automatically.
        $goodLooking->display();

        $this->assertTrue(class_exists('DummyFunctionHandlerAZ', false),
                          'Function handler "DummyFunctionHandlerAZ" not loaded');
        $this->assertTrue(class_exists('\\ns\\DummyFunctionHandlerBC', false),
                          'Function handler "DummyFunctionHandlerBC" not loaded');
        $this->assertFalse(class_exists('DummyFunctionHandlerDE', false),
                           'Function handler "DummyFunctionHandlerDE" loaded while not in use');
        $this->assertFalse(class_exists('\\ns\\DummyFunctionHandlerFG', false),
                           'Function handler "DummyFunctionHandlerFG" loaded while not in use');

        // For some reason, the normal tearDownAfterClass runs more than once when using @runTestsInSeparateProcesses
        self::_tearDownAfterClass();
    }
}

?>
