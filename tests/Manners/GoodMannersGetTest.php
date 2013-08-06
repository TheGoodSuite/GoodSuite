<?php

/** 
 * @runTestsInSeparateProcesses
 */
abstract class GoodMannersGetTest extends PHPUnit_Framework_TestCase
{
	private $store;
	
	abstract public function getNewStore();
	// this function should be removed, but is used for clearing the database at the moment
	abstract public function getNewDb();
	
	// This could be done just once for all the tests and it would even be necessary
	// to run the tests in this class in a single process.
	// However, since we can't run these tests in the same process as those from other
	// classes (we would have namespace collisions for Store and SQLStore)
	// we have to run every test in different class, and setUpBeforeClass doesn't
	// play well with that. As such, we'll have to call this function from
	// setUp instead of having PHPUnit do its magic.
	public static function _setUpBeforeClass()
	{
		// Garbage collector causes segmentation fault, so we disable 
		// for the duration of the test case
		gc_disable();
		file_put_contents(dirname(__FILE__) . '/../testInputFiles/GetType.datatype', 
																			"int myInt\n" .
																			"float myFloat\n".
																			"text myText\n" .
																			"datetime myDatetime\n" .
																			'"OtherType" myOtherType' . "\n" .
																			'"GetType" myCircular' . "\n");
		
		file_put_contents(dirname(__FILE__) . '/../testInputFiles/OtherType.datatype', 
																			"int yourInt\n");
	
		$rolemodel = new \Good\Rolemodel\Rolemodel();
		$schema = $rolemodel->createSchema(array('GetType' => dirname(__FILE__) . '/../testInputFiles/GetType.datatype',
												   'OtherType' => dirname(__FILE__) . '/../testInputFiles/OtherType.datatype'));

		$service = new \Good\Service\Service();
		$service->compile(array(new \Good\Manners\Modifier\Storable()), $schema, dirname(__FILE__) . '/../generated/');
		
		require dirname(__FILE__) . '/../generated/BaseGetType.datatype.php';
		require dirname(__FILE__) . '/../generated/BaseOtherType.datatype.php';
		
		$service->requireClasses(array('GetType', 'OtherType'));
		//require dirname(__FILE__) . '/../generated/GetType.datatype.php';
		
		require dirname(__FILE__) . '/../generated/GetTypeResolver.php';
		require dirname(__FILE__) . '/../generated/OtherTypeResolver.php';
	}
	
	public static function _tearDownAfterClass()
	{
		unlink(dirname(__FILE__) . '/../testInputFiles/GetType.datatype');
		unlink(dirname(__FILE__) . '/../testInputFiles/OtherType.datatype');
		unlink(dirname(__FILE__) . '/../generated/BaseGetType.datatype.php');
		unlink(dirname(__FILE__) . '/../generated/BaseOtherType.datatype.php');
		unlink(dirname(__FILE__) . '/../generated/GetType.datatype.php');
		unlink(dirname(__FILE__) . '/../generated/OtherType.datatype.php');
		unlink(dirname(__FILE__) . '/../generated/GetTypeResolver.php');
		unlink(dirname(__FILE__) . '/../generated/OtherTypeResolver.php');
		unlink(dirname(__FILE__) . '/../generated/GeneratedBaseClass.php');
		
		if (ini_get('zend.enable_gc'))
		{
			gc_enable();
		}
	}
	
	public function setUp()
	{
		$this->_setUpBeforeClass();
		
		// just doubling this up (from tearDown) to be sure
		// this should be handled natively once that is implemented
		$db = $this->getNewDb();
		$db->query('TRUNCATE gettype');
		$db->query('TRUNCATE othertype');
		
		$store = $this->getNewStore();
		
		$ins = new GetType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ref = new OtherType();
		$ref->setYourInt(90);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$store->insert($ins);
		
		$ins = new GetType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new OtherType();
		$ref->setYourInt(80);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$store->insert($ins);
		
		$ins = new GetType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new OtherType();
		$ref->setYourInt(40);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$store->insert($ins);
		
		$ins = new GetType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$store->insert($ins);
		
		$ins = new GetType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new OtherType();
		$ref->setYourInt(5);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$store->insert($ins);
		
		$store->flush();
		
		// new Store, so communication will have to go through data storage
		$this->store = $this->getNewStore();
	}
	
	public function tearDown()
	{
		// Just doing this already to make sure the deconstructor will hasve
		// side-effects at an unspecified moment...
		// (at which point the database will probably be in a wrong state for this)
		$this->store->flush();
		
		// this should be handled through the GoodManners API once that is implemented
		$db = $this->getNewDb();
		$db->query('TRUNCATE gettype');
		$db->query('TRUNCATE othertype');
		
		$this->_tearDownAfterClass();
	}
	
	private function array_search_specific($needle, $haystack)
	{
		// this is sort of a array_search
		// (it has to ignore any additional fields, though)
		foreach ($haystack as $key => $hay)
		{
			// I wanted to do strict checking here, but at the moment
			// all the values from the database are strings, so that's
			// not very useful.
			// I hope one day this'll be fixed, though.
			if ($hay->getMyInt() == $needle->getMyInt() &&
				$hay->getMyFloat() == $needle->getMyFloat() &&
				$hay->getMyText() == $needle->getMyText() &&
				$hay->getMyDatetime() == $needle->getMyDatetime() &&
				// they are both null
				(($hay->getMyOtherType() === null && $needle->getMyOtherType() === null) ||
				// or neither is null (so we won't be calling functions on null)
				// and they are the same
				 ($hay->getMyOtherType() !== null && $needle->getMyOtherType() !== null &&
				  $hay->getMyOtherType()->getYourInt() == $needle->getMyOtherType()->getYourInt())) &&
				// they are both null
				(($hay->getMyCircular() === null && $needle->getMyCircular() === null) ||
				// or neither is null (so we won't be calling functions on null)
				// and they are the same
				 ($hay->getMyCircular() !== null && $needle->getMyCircular() !== null &&
				  $hay->getMyCircular()->getMyInt() == $needle->getMyCircular()->getMyInt() &&
				  $hay->getMyCircular()->getMyFloat() == $needle->getMyCircular()->getMyFloat() &&
				  $hay->getMyCircular()->getMyText() == $needle->getMyCircular()->getMyText() &&
				  $hay->getMyCircular()->getMyDatetime() == $needle->getMyCircular()->getMyDatetime())))
			{
				return $key;
			}
		}
		
		return false;
	}
	
	private function assertContainsAndReturnIndex_specific($needle, $haystack)
	{
		$pos = $this->array_search_specific($needle, $haystack);
		
		if ($pos === false)
		
		{
			// this will always fail
			// basically, we tested before with a couple less restirctions
			// and now we just use the general function to get nice ouput
			// it'll contain some differences that don't matter, but that's 
			// a small price to pay
			$this->assertContains($needle, $haystack);
		}
		
		return $pos;
	}
	
	public function testGetAll()
	{
		// At the moment we don't have a proper api to get any,
		// but this trick does do the same
		$type = new GetType();
		$any = new \Good\Manners\Condition\Greater($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ref = new OtherType();
		$ref->setYourInt(90);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new OtherType();
		$ref->setYourInt(80);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new OtherType();
		$ref->setYourInt(40);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new OtherType();
		$ref->setYourInt(5);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);		
	}
	
	public function testGetLess()
	{
		$type = new GetType();
		$type->setMyInt(5);
		$any = new \Good\Manners\Condition\Less($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ref = new OtherType();
		$ref->setYourInt(90);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);
	}
	
	public function testGetLessOrEquals()
	{
		$type = new GetType();
		$type->setMyInt(5);
		$any = new \Good\Manners\Condition\LessOrEquals($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ref = new OtherType();
		$ref->setYourInt(90);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new OtherType();
		$ref->setYourInt(80);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);
	}
	
	public function testGetGreater()
	{
		$type = new GetType();
		$type->setMyInt(5);
		$any = new \Good\Manners\Condition\Greater($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new OtherType();
		$ref->setYourInt(40);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);
	}
	
	public function testGetGreaterOrEquals()
	{
		$type = new GetType();
		$type->setMyInt(5);
		$any = new \Good\Manners\Condition\GreaterOrEquals($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new OtherType();
		$ref->setYourInt(80);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new OtherType();
		$ref->setYourInt(40);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);
	}
	
	public function testGetEquality()
	{
		$type = new GetType();
		$type->setMyInt(5);
		$any = new \Good\Manners\Condition\Equality($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new OtherType();
		$ref->setYourInt(80);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);
	}
	
	public function testGetInequality()
	{
		$type = new GetType();
		$type->setMyInt(5);
		$any = new \Good\Manners\Condition\Inequality($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ref = new OtherType();
		$ref->setYourInt(90);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new OtherType();
		$ref->setYourInt(40);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);
	}
	
	/**
	 * @depends testGetEquality
	 */
	public function testGetEqualToReference()
	{
		// First, we get the referenced object
		// (we have to do this to get the id of the object,
		//  which is used when we compare to the specific reference,
		//  which is exactly what the whole point of this test is)
		$otherType = new OtherType();
		$otherType->setYourInt(80);
		
		$any = new \Good\Manners\Condition\Equality($otherType);
		
		$collection = $this->store->getCollection($any, new OtherTypeResolver());
		
		$referenced = $collection->getNext();
		
		// Then, we get the result with that reference	
		$type = new GetType();
		$type->setMyOtherType($referenced);
		$any = new \Good\Manners\Condition\Equality($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new OtherType();
		$ref->setYourInt(80);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);
	}
	
	/**
	 * @depends testGetAll
	 */
	public function testGetById()
	{
		// first we get a result from the database to find out what irs id is
		// We'll use the second from getAll, so it can't use any query similar
		// to how we originally get the result.
		
		// We still use the same ol' trick
		$type = new GetType();
		$any = new \Good\Manners\Condition\Equality($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$collection->getNext();
		$idHolder = $collection->getNext();
		
		// I want to make a better api for getting by id,
		// but this should do the trick
		$type = new GetType();
		$type->setId($idHolder->getId());
		$any = new \Good\Manners\Condition\Equality($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$expectedResults[] = $idHolder;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);		
	}
	
	/**
	 * @depends testGetLess
	 * @depends testGetGreater
	 */
	public function testGetAnd()
	{
		$type = new GetType();
		$type->setMyInt(4);
		$greater = new \Good\Manners\Condition\Greater($type);
		
		$type = new GetType();
		$type->setMyInt(10);
		$less = new \Good\Manners\Condition\Less($type);
		
		$and = new \Good\Manners\Condition\AndCondition($less, $greater);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($and, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new OtherType();
		$ref->setYourInt(80);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new OtherType();
		$ref->setYourInt(40);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);
	}
	
	/**
	 * @depends testGetLess
	 * @depends testGetGreater
	 */
	public function testGetOr()
	{
		$type = new GetType();
		$type->setMyInt(5);
		$greater = new \Good\Manners\Condition\Greater($type);
		
		$less = new \Good\Manners\Condition\Less($type);
		
		$type = new GetType();
		$type->setMyInt(8);
		$greater = new \Good\Manners\Condition\Greater($type);
		
		$and = new \Good\Manners\Condition\OrCondition($less, $greater);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($and, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ref = new OtherType();
		$ref->setYourInt(90);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);
	}
	
	public function testGetReferenceIsNull()
	{
		$type = new GetType();
		$type->setMyOtherType(null);
		$any = new \Good\Manners\Condition\Equality($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);
	}
	
	public function testGetReferenceIsNotNull()
	{
		$type = new GetType();
		$type->setMyOtherType(null);
		$any = new \Good\Manners\Condition\Inequality($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ref = new OtherType();
		$ref->setYourInt(90);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new OtherType();
		$ref->setYourInt(80);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new OtherType();
		$ref->setYourInt(40);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new OtherType();
		$ref->setYourInt(5);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);
	}
	
	/**
	 * @depends testGetLess
	 */
	public function testGetByPropertyOfReference()
	{
		$type = new GetType();
		$type->setMyOtherType(new OtherType());
		$type->getMyOtherType()->setYourInt(85);
		$any = new \Good\Manners\Condition\Less($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new OtherType();
		$ref->setYourInt(80);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new OtherType();
		$ref->setYourInt(40);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new OtherType();
		$ref->setYourInt(5);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);		
	}
	
	/**
	 * @depends testGetGreater
	 * @depends testGetByPropertyOfReference
	 */
	public function testGetByTwoValuesInOneCondition()
	{
		// At the moment we don't have a proper api to get any,
		// but this trick does do the same
		$type = new GetType();
		$type->setMyInt(4);
		$type->setMyOtherType(new OtherType());
		$type->getMyOtherType()->setYourInt(45);
		$any = new \Good\Manners\Condition\Greater($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new OtherType();
		$ref->setYourInt(80);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);		
	}
	
	/**
	 * @depends testGetGreater
	 */
	public function testGetByFloat()
	{
		// Ints are already tested as we use them above everywhere
		$type = new GetType();
		$type->setMyFloat(6.0);
		$any = new \Good\Manners\Condition\Greater($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new OtherType();
		$ref->setYourInt(40);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new OtherType();
		$ref->setYourInt(5);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);		
	}
	
	/**
	 * @depends testGetEquality
	 */
	public function testGetByText()
	{
		$type = new GetType();
		$type->setMyText("Twenty");
		$any = new \Good\Manners\Condition\Equality($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new OtherType();
		$ref->setYourInt(5);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);		
	}
	
	/**
	 * @depends testGetGreater
	 */
	public function testGetByDatetime()
	{
		$type = new GetType();
		$type->setMyDatetime(new Datetime('2006-06-06'));
		$any = new \Good\Manners\Condition\Greater($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new OtherType();
		$ref->setYourInt(40);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);
	}
	
	/**
	 * @depends testGetEquality
	 */
	public function testGetByIntIsNull()
	{
		$type = new GetType();
		$type->setMyInt(null);
		$any = new \Good\Manners\Condition\Equality($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new OtherType();
		$ref->setYourInt(5);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);		
	}
	
	/**
	 * @depends testGetEquality
	 */
	public function testGetByFloatIsNull()
	{
		$type = new GetType();
		$type->setMyFloat(null);
		$any = new \Good\Manners\Condition\Equality($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new OtherType();
		$ref->setYourInt(80);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);		
	}
	
	/**
	 * @depends testGetEquality
	 */
	public function testGetByTextIsNull()
	{
		$type = new GetType();
		$type->setMyText(null);
		$any = new \Good\Manners\Condition\Equality($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new OtherType();
		$ref->setYourInt(40);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);		
	}
	
	/**
	 * @depends testGetEquality
	 */
	public function testGetByDatetimeIsNull()
	{
		$type = new GetType();
		$type->setMyDatetime(null);
		$any = new \Good\Manners\Condition\Equality($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new OtherType();
		$ref->setYourInt(5);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);		
	}
	
	/**
	 * @depends testGetInequality
	 */
	public function testGetByIntIsNotNull()
	{
		// At the moment we don't have a proper api to get any,
		// but this trick does do the same
		$type = new GetType();
		$type->setMyInt(null);
		$any = new \Good\Manners\Condition\Inequality($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ref = new OtherType();
		$ref->setYourInt(90);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new OtherType();
		$ref->setYourInt(80);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new OtherType();
		$ref->setYourInt(40);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);		
	}
	
	/**
	 * @depends testGetInequality
	 */
	public function testGetByFloatIsNotNull()
	{
		$type = new GetType();
		$type->setMyFloat(null);
		$any = new \Good\Manners\Condition\Inequality($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ref = new OtherType();
		$ref->setYourInt(90);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new OtherType();
		$ref->setYourInt(40);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new OtherType();
		$ref->setYourInt(5);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);		
	}
	
	/**
	 * @depends testGetInequality
	 */
	public function testGetByTextIsNotNull()
	{
		$type = new GetType();
		$type->setMyText(null);
		$any = new \Good\Manners\Condition\Inequality($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ref = new OtherType();
		$ref->setYourInt(90);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new OtherType();
		$ref->setYourInt(80);
		$ins->setMyOtherType($ref);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new OtherType();
		$ref->setYourInt(5);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);		
	}
	
	/**
	 * @depends testGetInequality
	 */
	public function testGetByDatetimeIsNotNull()
	{
		$type = new GetType();
		$type->setMyDatetime(null);
		$any = new \Good\Manners\Condition\Inequality($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ref = new OtherType();
		$ref->setYourInt(90);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new OtherType();
		$ref->setYourInt(80);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new OtherType();
		$ref->setYourInt(40);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);		
	}
	
	/**
	 * @depends testGetAll
	 */
	public function testGetSortedAscending()
	{
		// Still the get any trick
		$type = new GetType();
		$any = new \Good\Manners\Condition\Greater($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$resolver->orderByMyIntAsc();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new OtherType();
		$ref->setYourInt(5);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$type = $collection->getNext();
		$this->assertContainsAndReturnIndex_specific($type, $expectedResults);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ref = new OtherType();
		$ref->setYourInt(90);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$type = $collection->getNext();
		$this->assertContainsAndReturnIndex_specific($type, $expectedResults);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new OtherType();
		$ref->setYourInt(80);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$type = $collection->getNext();
		$this->assertContainsAndReturnIndex_specific($type, $expectedResults);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new OtherType();
		$ref->setYourInt(40);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$type = $collection->getNext();
		$this->assertContainsAndReturnIndex_specific($type, $expectedResults);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$type = $collection->getNext();
		$this->assertContainsAndReturnIndex_specific($type, $expectedResults);
		
		$this->assertSame(null, $collection->getNext());
	}
	
	/**
	 * @depends testGetAll
	 */
	public function testGetSortedDescending()
	{
		// Still the get any trick
		$type = new GetType();
		$any = new \Good\Manners\Condition\Greater($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$resolver->orderByMyIntDesc();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$type = $collection->getNext();
		$this->assertContainsAndReturnIndex_specific($type, $expectedResults);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new OtherType();
		$ref->setYourInt(40);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$type = $collection->getNext();
		$this->assertContainsAndReturnIndex_specific($type, $expectedResults);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new OtherType();
		$ref->setYourInt(80);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$type = $collection->getNext();
		$this->assertContainsAndReturnIndex_specific($type, $expectedResults);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ref = new OtherType();
		$ref->setYourInt(90);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$type = $collection->getNext();
		$this->assertContainsAndReturnIndex_specific($type, $expectedResults);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new OtherType();
		$ref->setYourInt(5);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$type = $collection->getNext();
		$this->assertContainsAndReturnIndex_specific($type, $expectedResults);
		
		$this->assertSame(null, $collection->getNext());
	}
	
	/**
	 * @depends testGetAll
	 * @depends testGetSortedAscending
	 * @depends testGetSortedDescending
	 */
	public function testGetDoubleSorted()
	{
		// Still the get any trick
		$type = new GetType();
		$any = new \Good\Manners\Condition\Greater($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyOtherType();
		$resolver->orderByMyFloatAsc();
		$resolver->orderByMyIntDesc();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new OtherType();
		$ref->setYourInt(80);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$type = $collection->getNext();
		$this->assertContainsAndReturnIndex_specific($type, $expectedResults);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ref = new OtherType();
		$ref->setYourInt(90);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$type = $collection->getNext();
		$this->assertContainsAndReturnIndex_specific($type, $expectedResults);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$type = $collection->getNext();
		$this->assertContainsAndReturnIndex_specific($type, $expectedResults);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new OtherType();
		$ref->setYourInt(40);
		$ins->setMyOtherType($ref);
		$expectedResults[] = $ins;
		
		$type = $collection->getNext();
		$this->assertContainsAndReturnIndex_specific($type, $expectedResults);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new OtherType();
		$ref->setYourInt(5);
		$ins->setMyOtherType($ref);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$type = $collection->getNext();
		$this->assertContainsAndReturnIndex_specific($type, $expectedResults);
		
		$expectedResults = array();
		
		$this->assertSame(null, $collection->getNext());
	}
	
	/**
	 * @depends testGetAll
	 */
	public function testGetAllUnresolvedReference()
	{
		// same ol' trick for getting any
		$type = new GetType();
		$any = new \Good\Manners\Condition\Greater($type);
		
		$resolver = new GetTypeResolver();
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			// do an isResolved check here
			// and then set to null (because you can't access unresolved properties)
			// However, the first isn't possible yet and the second isn't necessary yet
			
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);
	}
	
	/**
	 * @depends testGetAll
	 */
	public function testGetCircularReference()
	{
		// First, we need to create a circular reference:
		$type = new GetType();
		$any = new \Good\Manners\Condition\Greater($type);
		$resolver = new GetTypeResolver();
		$resolver->orderByMyIntAsc();
		$collection = $this->store->getCollection($any, $resolver);
		while ($type = $collection->getNext())
		{
			if ($type->getMyInt() == 4)
			{
				$ref = $type;
			}
			else if ($type->getMyInt() == 10)
			{
				$ref->setMyCircular($type);
				$type->setMyCircular($ref);
			}
		}
		$this->store->flush();
		
		// same ol' trick for getting any
		$type = new GetType();
		$any = new \Good\Manners\Condition\Greater($type);
		
		$resolver = new GetTypeResolver();
		$resolver->resolveMyCircular();
		
		$collection = $this->store->getCollection($any, $resolver);
		
		$expectedResults = array();
		
		$ins = new GetType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyOtherType(null);
		$expectedResults[] = $ins;
		$int4 = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		$ins = new GetType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ins->setMyOtherType(null);
		$ins->setMyCircular($int4);
		$expectedResults[] = $ins;
		$int4->setMyCircular($ins);
		
		$ins = new GetType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ins->setMyOtherType(null);
		$ins->setMyCircular(null);
		$expectedResults[] = $ins;
		
		while ($type = $collection->getNext())
		{
			// do an isResolved check here
			// and then set to null (because you can't access unresolved properties)
			// However, the first isn't possible yet and the second isn't necessary yet
			
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
			
			array_splice($expectedResults, $pos, 1);
		}
		
		$this->assertSame(array(), $expectedResults);
	}
}

?>