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
			require $this->outputDir . 'Base' . ucfirst($type) . '.datatype.php';
		}
		
		$service->requireClasses(array_keys($types));
		$this->files = array_merge($this->files, array_values($types));
		
		foreach ($types as $type => $path)
		{
			$this->files[] = $this->outputDir . 'Base' . ucfirst($type) . '.datatype.php';
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
		$myType->setMyInt(5);
		
		$this->assertEquals($myType->getMyInt(), 5);
		$this->assertInternalType('int', $myType->getMyInt());
	}
	
	public function testFloatProperty()
	{
		file_put_contents($this->inputDir . 'MyType.datatype',
							'float myFloat');
		$this->compile(array('MyType' => $this->inputDir . 'MyType.datatype'));
		
		$myType = new MyType();
		$myType->setMyFloat(5.5);
		
		$this->assertEquals($myType->getMyFloat(), 5.5);
		$this->assertInternalType('float', $myType->getMyFloat());
	}
	
	public function testTextProperty()
	{
		file_put_contents($this->inputDir . 'MyType.datatype',
							'text myText');
		$this->compile(array('MyType' => $this->inputDir . 'MyType.datatype'));
		
		$myType = new MyType();
		$myType->setMyText("Hello World!");
		
		$this->assertEquals($myType->getMyText(), "Hello World!");
		$this->assertInternalType('string', $myType->getMyText());
	}
	
	public function testDatetimeProperty()
	{
		file_put_contents($this->inputDir . 'MyType.datatype',
							'datetime myDatetime');
		$this->compile(array('MyType' => $this->inputDir . 'MyType.datatype'));
		
		$myType = new MyType();
		$date = new \DateTime();
		$myType->setMyDatetime($date);
		
		$this->assertEquals($myType->getMyDatetime(), $date);
		$this->assertInstanceOf('\\DateTime', $myType->getMyDatetime());
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
		$reference->setJustAnInt(45);
		
		$referer = new ReferenceType();
		$referer->setReference($reference);
		
		$this->assertEquals($referer->getReference(), $reference);
		$this->assertInstanceOf('ReferencedType', $referer->getReference());
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
		$myType->setMyInt(5);
		
		$this->assertEquals($myType->getMyInt(), 5);
		$this->assertInternalType('int', $myType->getMyInt());
	}
	
	/**
	 * @depends testIntProperty
	 */
	public function testPrivateProperty()
	{
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
	 */
	public function testProtectedProperty()
	{
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
	 */
	public function testPublicProperty()
	{
		file_put_contents($this->inputDir . 'MyType.datatype',
							'[public] int myInt');
		$this->compile(array('MyType' => $this->inputDir . 'MyType.datatype'));
		
		// Private is actually protected...
		$reflection = new \ReflectionMethod('MyType', 'getMyInt');
		$this->assertTrue($reflection->isPublic());
		$reflection = new \ReflectionMethod('MyType', 'setMyInt');
		$this->assertTrue($reflection->isPublic());
	}
}

?>