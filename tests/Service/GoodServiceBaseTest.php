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
    
    protected function compile($inputfiles, $modifiers = array())
    {
        $rolemodel = new \Good\Rolemodel\Rolemodel();

        $schema = $rolemodel->createSchema($inputfiles);

        $service = new \Good\Service\Service();

        $outputFiles = $service->compile($modifiers, $schema, $this->outputDir);
        
        foreach ($outputFiles as $file)
        {
            require $file;
        }
        
        $this->files = array_merge($this->files, $inputfiles, $outputFiles);
        
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
                            'datatype MyType { int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $myType->myInt = 5;
        
        $this->assertEquals($myType->myInt, 5);
        $this->assertInternalType('int', $myType->myInt);
    }
    
    public function testFloatProperty()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { float myFloat; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $myType->myFloat = 5.5;
        
        $this->assertEquals($myType->myFloat, 5.5);
        $this->assertInternalType('float', $myType->myFloat);
    }
    
    public function testTextProperty()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text myText; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $myType->myText = "Hello World!";
        
        $this->assertEquals($myType->myText, "Hello World!");
        $this->assertInternalType('string', $myType->myText);
    }
    
    public function testDatetimeProperty()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { datetime myDatetime; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $date = new \DateTime();
        $myType->myDatetime = $date;
        
        $this->assertEquals($myType->myDatetime, $date);
        $this->assertInstanceOf('\\DateTime', $myType->myDatetime);
    }
    
    public function testReferenceProperty()
    {
        file_put_contents($this->inputDir . 'ReferencedType.datatype',
                            'datatype ReferencedType { int justAnInt; }');
        
        file_put_contents($this->inputDir . 'ReferenceType.datatype',
                            'datatype ReferenceType { "ReferencedType" reference; }');
                            
        $this->compile(array($this->inputDir . 'ReferencedType.datatype',
                             $this->inputDir . 'ReferenceType.datatype'));
        
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
    public function testIntGetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $myType->myInt = 5;
        
        $this->assertEquals($myType->getMyInt(), 5);
        $this->assertInternalType('int', $myType->myInt);
    }
    
    /**
     * @depends testFloatProperty
     */
    public function testFloatGetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { float myFloat; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $myType->myFloat = 5.5;
        
        $this->assertEquals($myType->getMyFloat(), 5.5);
        $this->assertInternalType('float', $myType->myFloat);
    }
    
    /**
     * @depends testTextProperty
     */
    public function testTextGetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text myText; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $myType->myText = "Hello World!";
        
        $this->assertEquals($myType->getMyText(), "Hello World!");
        $this->assertInternalType('string', $myType->myText);
    }
    
    /**
     * @depends testDatetimeProperty
     */
    public function testDatetimeGetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { datetime myDatetime; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $date = new \DateTime();
        $myType->myDatetime = $date;
        
        $this->assertEquals($myType->getMyDatetime(), $date);
        $this->assertInstanceOf('\\DateTime', $myType->myDatetime);
    }
    
    /**
     * @depends testReferenceProperty
     */
    public function testReferenceGetter()
    {
        file_put_contents($this->inputDir . 'ReferencedType.datatype',
                            'datatype ReferencedType { int justAnInt; }');
        
        file_put_contents($this->inputDir . 'ReferenceType.datatype',
                            'datatype ReferenceType { "ReferencedType" reference; }');
                            
        $this->compile(array($this->inputDir . 'ReferencedType.datatype',
                             $this->inputDir . 'ReferenceType.datatype'));
        
        $reference = new ReferencedType();
        $reference->justAnInt = 45;
        
        $referer = new ReferenceType();
        $referer->reference = $reference;
        
        $this->assertEquals($referer->getReference(), $reference);
        $this->assertInstanceOf('ReferencedType', $referer->reference);
    }
    
    /**
     * @depends testIntProperty
     */
    public function testIntSetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $myType->setMyInt(5);
        
        $this->assertEquals($myType->myInt, 5);
        $this->assertInternalType('int', $myType->myInt);
    }
    
    /**
     * @depends testFloatProperty
     */
    public function testFloatSetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { float myFloat; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $myType->setMyFloat(5.5);
        
        $this->assertEquals($myType->myFloat, 5.5);
        $this->assertInternalType('float', $myType->myFloat);
    }
    
    /**
     * @depends testTextProperty
     */
    public function testTextSetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text myText; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $myType->setMyText("Hello World!");
        
        $this->assertEquals($myType->myText, "Hello World!");
        $this->assertInternalType('string', $myType->myText);
    }
    
    /**
     * @depends testDatetimeProperty
     */
    public function testDatetimeSetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { datetime myDatetime; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $date = new \DateTime();
        $myType->setMyDatetime($date);
        
        $this->assertEquals($myType->myDatetime, $date);
        $this->assertInstanceOf('\\DateTime', $myType->myDatetime);
    }
    
    /**
     * @depends testReferenceProperty
     */
    public function testReferenceSetter()
    {
        file_put_contents($this->inputDir . 'ReferencedType.datatype',
                            'datatype ReferencedType { int justAnInt; }');
        
        file_put_contents($this->inputDir . 'ReferenceType.datatype',
                            'datatype ReferenceType { "ReferencedType" reference; }');
                            
        $this->compile(array($this->inputDir . 'ReferencedType.datatype',
                             $this->inputDir . 'ReferenceType.datatype'));
        
        $reference = new ReferencedType();
        $reference->justAnInt = 45;
        
        $referer = new ReferenceType();
        $referer->setReference($reference);
        
        $this->assertEquals($referer->reference, $reference);
        $this->assertInstanceOf('ReferencedType', $referer->reference);
    }
    
    /**
     * @depends testIntProperty
     */
    public function testPHPTags()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            '<?php datatype MyType { int myInt; } ?>');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $myType->myInt = 5;
        
        $this->assertEquals($myType->myInt, 5);
        $this->assertInternalType('int', $myType->myInt);
    }
    
    /**
     * @depends testIntProperty
     */
    public function testPrivateGetterSetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { [private] int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        // Private is actually protected...
        $reflection = new \ReflectionMethod('MyType', 'getMyInt');
        $this->assertTrue($reflection->isProtected());
        $reflection = new \ReflectionMethod('MyType', 'setMyInt');
        $this->assertTrue($reflection->isProtected());
    }
    
    /**
     * @depends testIntProperty
     */
    public function testThereIsNoPrivatePropertyGet()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { [private] int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        // Private is actually protected...
        $val = new MyType();
        
        $this->setExpectedException('Exception', 'Unknown or non-public property');
        $int = $val->myInt;
    }
    
    /**
     * @depends testIntProperty
     */
    public function testThereIsNoPrivatePropertySet()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { [private] int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        // Private is actually protected...
        $val = new MyType();
        
        $this->setExpectedException('Exception', 'Unknown or non-public property');
        $val->myInt = 4;
    }
    
    /**
     * @depends testIntProperty
     */
    public function testProtectedGetterSetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { [protected] int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        $reflection = new \ReflectionMethod('MyType', 'getMyInt');
        $this->assertTrue($reflection->isProtected());
        $reflection = new \ReflectionMethod('MyType', 'setMyInt');
        $this->assertTrue($reflection->isProtected());
    }
    
    /**
     * @depends testIntProperty
     */
    public function testThereIsNoProtectedPropertyGet()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { [private] int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        // Private is actually protected...
        $val = new MyType();
        
        $this->setExpectedException('Exception', 'Unknown or non-public property');
        $int = $val->myInt;
    }
    
    /**
     * @depends testIntProperty
     */
    public function testThereIsNoProtectedPropertySet()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { [private] int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        // Private is actually protected...
        $val = new MyType();
        
        $this->setExpectedException('Exception', 'Unknown or non-public property');
        $val->myInt = 4;
    }
    
    /**
     * @depends testIntProperty
     */
    public function testPublicGetterSetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { [public] int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        // Private is actually protected...
        $reflection = new \ReflectionMethod('MyType', 'getMyInt');
        $this->assertTrue($reflection->isPublic());
        $reflection = new \ReflectionMethod('MyType', 'setMyInt');
        $this->assertTrue($reflection->isPublic());
    }
    
    /**
     * @depends testIntProperty
     */
    public function testDefaultIsPublicGetterSetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        // Private is actually protected...
        $reflection = new \ReflectionMethod('MyType', 'getMyInt');
        $this->assertTrue($reflection->isPublic());
        $reflection = new \ReflectionMethod('MyType', 'setMyInt');
        $this->assertTrue($reflection->isPublic());
    }
    
    public function testMultipleMembers()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            "datatype MyType\n".
                            "{\n".
                            "   int myInt;\n" .
                            "   int myFloat;\n" .
                            "   int myInt2;\n" .
                            "}");
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $myType->myInt = 5;
        $this->assertEquals($myType->myInt, 5);
        $this->assertInternalType('int', $myType->myInt);
        
        $myType = new MyType();
        $myType->myFloat = 5.5;
        $this->assertEquals($myType->myFloat, 5.5);
        $this->assertInternalType('float', $myType->myFloat);
        
        $myType = new MyType();
        $myType->myInt2 = 42;
        $this->assertEquals($myType->myInt2, 42);
        $this->assertInternalType('int', $myType->myInt2);
    }
    
    public function testMultipleMembersOnOneLine()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            "datatype MyType{int myInt;".
                            "[]int myFloat;".
                            "int myInt2;}");
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $myType->myInt = 5;
        $this->assertEquals($myType->myInt, 5);
        $this->assertInternalType('int', $myType->myInt);
        
        $myType = new MyType();
        $myType->myFloat = 5.5;
        $this->assertEquals($myType->myFloat, 5.5);
        $this->assertInternalType('float', $myType->myFloat);
        
        $myType = new MyType();
        $myType->myInt2 = 42;
        $this->assertEquals($myType->myInt2, 42);
        $this->assertInternalType('int', $myType->myInt2);
    }
    
    public function testOneMemberSpreadOverMultipleLines()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            "datatype\n" .
                            "MyType\n" .
                            "{\n" .
                            "[\n" .
                            "public\n".
                            "]\n".
                            "int   \n".
                            "myInt\n" .
                            ";\n" .
                            "}\n");
        $this->compile(array($this->inputDir . 'MyType.datatype'));
        
        $myType = new MyType();
        $myType->myInt = 5;
        $this->assertEquals($myType->myInt, 5);
        $this->assertInternalType('int', $myType->myInt);
    }
}

?>