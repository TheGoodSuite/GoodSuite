<?php

class Error1Test extends PHPUnit_Framework_TestCase
{
    public function testCompileTemplate()
    {
        $this->setExpectedException('Exception');
        
        include 'Exception.php';
    }
}

?>