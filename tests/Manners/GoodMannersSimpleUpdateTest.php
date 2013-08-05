<?php

/** 
 * @runTestsInSeparateProcesses
 */
abstract class GoodMannersSimpleUpdateTest extends PHPUnit_Framework_TestCase
{
	private $store1;
	private $store2;
	
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
		file_put_contents(dirname(__FILE__) . '/../testInputFiles/SimpleUpdateType.datatype', 
																			"int myInt\n" .
																			"float myFloat\n".
																			"text myText\n" .
																			"datetime myDatetime\n" .
																			'"AnotherType" myReference' . "\n");
		
		file_put_contents(dirname(__FILE__) . '/../testInputFiles/AnotherType.datatype', 
																			"int yourInt\n");
	
		$rolemodel = new \Good\Rolemodel\Rolemodel();
		$schema = $rolemodel->createSchema(array('SimpleUpdateType' => 
														dirname(__FILE__) . '/../testInputFiles/SimpleUpdateType.datatype',
												   'AnotherType' => 
														dirname(__FILE__) . '/../testInputFiles/AnotherType.datatype'));

		$service = new \Good\Service\Service();
		$service->compile(array(new \Good\Manners\Modifier\Storable()), $schema, dirname(__FILE__) . '/../generated/');
		
		require dirname(__FILE__) . '/../generated/BaseSimpleUpdateType.datatype.php';
		require dirname(__FILE__) . '/../generated/BaseAnotherType.datatype.php';
		
		$service->requireClasses(array('SimpleUpdateType', 'AnotherType'));
		
		$manners = new \Good\Manners\Manners();
		$manners->compileStore($schema, dirname(__FILE__) . '/../generated/');
		require dirname(__FILE__) . '/../generated/Store.php';

		$memory = new \Good\Memory\Memory();
		$memory->compileSQLStore($schema, dirname(__FILE__) . '/../generated/');
		require dirname(__FILE__) . '/../generated/SQLStore.php';
		
		require dirname(__FILE__) . '/../generated/SimpleUpdateTypeResolver.php';
		require dirname(__FILE__) . '/../generated/AnotherTypeResolver.php';
	}
	
	public static function _tearDownAfterClass()
	{
		unlink(dirname(__FILE__) . '/../testInputFiles/SimpleUpdateType.datatype');
		unlink(dirname(__FILE__) . '/../testInputFiles/AnotherType.datatype');
		unlink(dirname(__FILE__) . '/../generated/BaseSimpleUpdateType.datatype.php');
		unlink(dirname(__FILE__) . '/../generated/BaseAnotherType.datatype.php');
		unlink(dirname(__FILE__) . '/../generated/SimpleUpdateType.datatype.php');
		unlink(dirname(__FILE__) . '/../generated/AnotherType.datatype.php');
		unlink(dirname(__FILE__) . '/../generated/SimpleUpdateTypeCollection.php');
		unlink(dirname(__FILE__) . '/../generated/AnotherTypeCollection.php');
		unlink(dirname(__FILE__) . '/../generated/SimpleUpdateTypeResolver.php');
		unlink(dirname(__FILE__) . '/../generated/AnotherTypeResolver.php');
		unlink(dirname(__FILE__) . '/../generated/GeneratedBaseClass.php');
		unlink(dirname(__FILE__) . '/../generated/Store.php');
		unlink(dirname(__FILE__) . '/../generated/SQLStore.php');
		
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
		$db->query('TRUNCATE simpleupdatetype');
		$db->query('TRUNCATE anothertype');
		
		$store = $this->getNewStore();
		
		$ins = new SimpleUpdateType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ref = new AnotherType();
		$ref->setYourInt(50);
		$ins->setMyReference($ref);
		$store->insertSimpleUpdateType($ins);
		
		$ins = new SimpleUpdateType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new AnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$store->insertSimpleUpdateType($ins);
		
		$ins = new SimpleUpdateType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new AnotherType();
		$ref->setYourInt(30);
		$ins->setMyReference($ref);
		$store->insertSimpleUpdateType($ins);
		
		$ins = new SimpleUpdateType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ref = new AnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$store->insertSimpleUpdateType($ins);
		
		$ins = new SimpleUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new AnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$store->insertSimpleUpdateType($ins);
		
		$store->flush();
		
		// new Store, so communication will have to go through data storage
		$this->store1 = $this->getNewStore();
		$this->store2 = $this->getNewStore();
	}
	
	public function tearDown()
	{
		// Just doing this already to make sure the deconstructor will hasve
		// side-effects at an unspecified moment...
		// (at which point the database will probably be in a wrong state for this)
		$this->store1->flush();
		$this->store2->flush();
		
		// this should be handled through the GoodManners API once that is implemented
		$db = $this->getNewDb();
		$db->query('TRUNCATE simpleupdatetype');
		$db->query('TRUNCATE anothertype');
		
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
				(($hay->getMyReference() === null && $needle->getMyReference() === null) ||
				// or neither is null (so we won't be calling functions on null)
				// and they are the same
				 ($hay->getMyReference() !== null && $needle->getMyReference() !== null &&
				  $hay->getMyReference()->getYourInt() == $needle->getMyReference()->getYourInt())))
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
	
	private function checkResults($expected)
	{
		// At the moment we don't have a proper api to get any,
		// but this trick does do the same
		$type = new SimpleUpdateType();
		$any = new \Good\Manners\Condition\Greater($type);
		
		$resolver = new SimpleUpdateTypeResolver();
		$resolver->resolveMyReference();
		$collection = $this->store2->getSimpleUpdateTypeCollection($any, $resolver);
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expected);
			
			array_splice($expected, $pos, 1);
		}
		
		$this->assertSame(array(), $expected);		
	}
	
	public function testSimpleUpdate()
	{
		// At the moment we don't have a proper api to get any,
		// but this trick does do the same
		$type = new SimpleUpdateType();
		$any = new \Good\Manners\Condition\Greater($type);
		
		$resolver = new SimpleUpdateTypeResolver();
		$resolver->resolveMyReference();
		$collection = $this->store1->getSimpleUpdateTypeCollection($any, $resolver);
		
		while ($type = $collection->getNext())
		{
			$type->setMyInt(2);
			$type->setMyFloat(1.1);
			$type->setMyText("Zero");
			$type->setMyDatetime(new Datetime('1999-12-31'));
		}
		
		$this->store1->flush();
		
		$expectedResults = array();
		
		$ins = new SimpleUpdateType();
		$ins->setMyInt(2);
		$ins->setMyFloat(1.1);
		$ins->setMyText("Zero");
		$ins->setMyDatetime(new Datetime('1999-12-31'));
		$ins->setMyReference(new AnotherType());
		$ins->getMyReference()->setYourInt(50);
		$expectedResults[] = $ins;
		
		$ins = new SimpleUpdateType();
		$ins->setMyInt(2);
		$ins->setMyFloat(1.1);
		$ins->setMyText("Zero");
		$ins->setMyDatetime(new Datetime('1999-12-31'));
		$ins->setMyReference(new AnotherType());
		$ins->getMyReference()->setYourInt(40);
		$expectedResults[] = $ins;
		
		$ins = new SimpleUpdateType();
		$ins->setMyInt(2);
		$ins->setMyFloat(1.1);
		$ins->setMyText("Zero");
		$ins->setMyDatetime(new Datetime('1999-12-31'));
		$ins->setMyReference(new AnotherType());
		$ins->getMyReference()->setYourInt(30);
		$expectedResults[] = $ins;
		
		$ins = new SimpleUpdateType();
		$ins->setMyInt(2);
		$ins->setMyFloat(1.1);
		$ins->setMyText("Zero");
		$ins->setMyDatetime(new Datetime('1999-12-31'));
		$ins->setMyReference(new AnotherType());
		$ins->getMyReference()->setYourInt(20);
		$expectedResults[] = $ins;
		
		$ins = new SimpleUpdateType();
		$ins->setMyInt(2);
		$ins->setMyFloat(1.1);
		$ins->setMyText("Zero");
		$ins->setMyDatetime(new Datetime('1999-12-31'));
		$ins->setMyReference(new AnotherType());
		$ins->getMyReference()->setYourInt(10);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
	
	public function testSimpleUpdateSetToNull()
	{
		// At the moment we don't have a proper api to get any,
		// but this trick does do the same
		$type = new SimpleUpdateType();
		$any = new \Good\Manners\Condition\Greater($type);
		
		$resolver = new SimpleUpdateTypeResolver();
		$resolver->resolveMyReference();
		$collection = $this->store1->getSimpleUpdateTypeCollection($any, $resolver);
		
		while ($type = $collection->getNext())
		{
			if ($type->getMyInt() == 5)
			{
				$type->setMyInt(null);
				$type->setMyText(null);
			}
			
			if ($type->getMyInt() == 10)
			{
				$type->setMyFloat(null);
				$type->setMyDatetime(null);
			}
		}
		
		$this->store1->flush();
		
		$expectedResults = array();
		
		$ins = new SimpleUpdateType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ref = new AnotherType();
		$ref->setYourInt(50);
		$ins->setMyReference($ref);
		$expectedResults[] = $ins;
		
		$ins = new SimpleUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(null);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new AnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$expectedResults[] = $ins;
		
		$ins = new SimpleUpdateType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new AnotherType();
		$ref->setYourInt(30);
		$ins->setMyReference($ref);
		$expectedResults[] = $ins;
		
		$ins = new SimpleUpdateType();
		$ins->setMyInt(10);
		$ins->setMyFloat(null);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(null);
		$ref = new AnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$expectedResults[] = $ins;
		
		$ins = new SimpleUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new AnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
	
	public function testSimpleUpdateReferences()
	{
		// At the moment we don't have a proper api to get any,
		// but this trick does do the same
		$type = new SimpleUpdateType();
		$any = new \Good\Manners\Condition\Greater($type);
		
		$resolver = new SimpleUpdateTypeResolver();
		$resolver->resolveMyReference();
		$resolver->orderByMyIntAsc();
		$collection = $this->store1->getSimpleUpdateTypeCollection($any, $resolver);
		
		$ref = null;
		
		while ($type = $collection->getNext())
		{
			if ($type->getMyInt() == 8)
			{
				$ref = $type->getMyReference();
				$type->setMyReference(null);
			}
			
			if ($type->getMyInt() == 10)
			{
				$type->setMyReference($ref);
			}
			
			if ($type->getMyFloat() == 20.20)
			{
				$myref = new AnotherType();
				$myref->setYourInt(144);
				$type->setMyReference($myref);
				
				// todo: make this line unnecessary
				$this->store1->insertAnotherType($myref);
			}
		}
		
		$this->store1->flush();
		
		$expectedResults = array();
		
		$ins = new SimpleUpdateType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ref = new AnotherType();
		$ref->setYourInt(50);
		$ins->setMyReference($ref);
		$expectedResults[] = $ins;
		
		$ins = new SimpleUpdateType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new AnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$expectedResults[] = $ins;
		
		$ins = new SimpleUpdateType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ins->setMyReference(null);
		$expectedResults[] = $ins;
		
		$ins = new SimpleUpdateType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ref = new AnotherType();
		$ref->setYourInt(30);
		$ins->setMyReference($ref);
		$expectedResults[] = $ins;
		
		$ins = new SimpleUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new AnotherType();
		$ref->setYourInt(144);
		$ins->setMyReference($ref);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
}

?>