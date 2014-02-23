<?php

// Making abstract so I can have a child that does exactly this, and one
// that does repeat these tests with a modifier, to confirm everything is
// still working with that modifier 
// (PHPUnit doesn't like it if I do this without the base class being 
//  abstract)

/**
 * @runTestsInSeparateProcesses
 */
abstract class GoodServiceBaseTest extends PHPUnit_Framework_TestCase
{
    private $files = array(); // so we know what to clean up
    protected $inputDir = '';
    private $outputDir = '';
    
    public function setUp()
    {
        $this->inputDir = dirname(__FILE__) . '/../testInputFiles/';
        $this->outputDir =  dirname(__FILE__) . '/../generated/';
    }
    
    protected function compile($types, $modifiers = array())
    {
        $rolemodel = new \Good\Rolemodel\Rolemodel();

        $schema = $rolemodel->createSchema($types);

        $service = new \Good\Service\Service();

        $service->compile($modifiers, $schema, $this->outputDir);
        
        foreach ($types as $type => $path)
        {
            require $this->outputDir . $type . '.datatype.php';
        }
        
        $this->files = array_merge($this->files, array_values($types));
        
        foreach ($types as $type => $path)
        {
            $this->files[] = $this->outputDir . $type . '.datatype.php';
        }
        
        $this->files[] = $this->outputDir . 'GeneratedBaseClass.php';
    }
    
    public function tearDown()
    {
        foreach($this->files as $file)
        {
            unlink($file);
        }
        
        // not strictly necessary, but I'd rather not rely ont he fact
        // that each test runs in a different instance fo this class
        $this->files = array();
    }
    
    public function testIntProperty()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'int myInt');
        $this->compile(array('MyType' => $this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $myType->myInt = 5;
        
        $this->assertEquals($myType->myInt, 5);
        $this->assertInternalType('int', $myType->myInt);
    }
    
    public function testFloatProperty()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'float myFloat');
        $this->compile(array('MyType' => $this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $myType->myFloat = 5.5;
        
        $this->assertEquals($myType->myFloat, 5.5);
        $this->assertInternalType('float', $myType->myFloat);
    }
    
    public function testTextProperty()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'text myText');
        $this->compile(array('MyType' => $this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $myType->myText = "Hello World!";
        
        $this->assertEquals($myType->myText, "Hello World!");
        $this->assertInternalType('string', $myType->myText);
    }
    
    public function testDatetimeProperty()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datetime myDatetime');
        $this->compile(array('MyType' => $this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $date = new \DateTime();
        $myType->myDatetime = $date;
        
        $this->assertEquals($myType->myDatetime, $date);
        $this->assertInstanceOf('\\DateTime', $myType->myDatetime);
    }
    
    public function testReferenceProperty()
    {
        file_put_contents($this->inputDir . 'ReferencedType.datatype',
                            'int justAnInt');
        
        file_put_contents($this->inputDir . 'ReferenceType.datatype',
                            '"ReferencedType" reference');
                            
        $this->compile(array('ReferencedType' => $this->inputDir . 'ReferencedType.datatype',
                             'ReferenceType' => $this->inputDir . 'ReferenceType.datatype'));
        
        $reference = new ReferencedType();
        $reference->justAnInt = 45;
        
        $referer = new ReferenceType();
        $referer->reference = $reference;
        
        $this->assertEquals($referer->reference, $reference);
        $this->assertInstanceOf('ReferencedType', $referer->reference);
    }
    
    /**
     * @depends testIntProperty
     */
    public function testPHPTags()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            '<?php int myInt ?>');
        $this->compile(array('MyType' => $this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $myType->myInt = 5;
        
        $this->assertEquals($myType->myInt, 5);
        $this->assertInternalType('int', $myType->myInt);
    }
    
    /**
     * @depends testIntProperty
     *
    public function testThereIsNoPrivateBug()
    {
        // First off, there is no more implementation of private (see #62)
        // Secondly, this way of testing no longer works at all, so we'll just fail.
        $this->assertTrue(false);
        file_put_contents($this->inputDir . 'MyType.datatype',
                            '[private] int myInt');
        $this->compile(array('MyType' => $this->inputDir . 'MyType.datatype'));
        
        // Private is actually protected...
        $reflection = new \ReflectionMethod('MyType', 'getMyInt');
        $this->assertTrue($reflection->isProtected());
        $reflection = new \ReflectionMethod('MyType', 'setMyInt');
        $this->assertTrue($reflection->isProtected());
    }
    
    /**
     * @depends testIntProperty
     *
    public function testThereIsNoProtectedBug()
    {
        // First off, there is no more implementation of protected (see #62)
        // Secondly, this way of testing no longer works at all, so we'll just fail.
        $this->assertTrue(false);
        file_put_contents($this->inputDir . 'MyType.datatype',
                            '[protected] int myInt');
        $this->compile(array('MyType' => $this->inputDir . 'MyType.datatype'));
        
        // Private is actually protected...
        $reflection = new \ReflectionMethod('MyType', 'getMyInt');
        $this->assertTrue($reflection->isProtected());
        $reflection = new \ReflectionMethod('MyType', 'setMyInt');
        $this->assertTrue($reflection->isProtected());
    }
    
    /**
     * @depends testIntProperty
     *
    public function testPublicProperty()
    {
        // This can't be tested this way anymore, but I'll leave this test for
        // until I think of a proper way to test for this (and perhaps for when #62 is solved)
        file_put_contents($this->inputDir . 'MyType.datatype',
                            '[public] int myInt');
        $this->compile(array('MyType' => $this->inputDir . 'MyType.datatype'));
        
        // Private is actually protected...
        $reflection = new \ReflectionMethod('MyType', 'getMyInt');
        $this->assertTrue($reflection->isPublic());
        $reflection = new \ReflectionMethod('MyType', 'setMyInt');
        $this->assertTrue($reflection->isPublic());
    }
    */
}

?>