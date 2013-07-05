<?php

/** 
 * @runTestsInSeparateProcesses
 */
abstract class GoodMannersAdvancedUpdateTest extends PHPUnit_Framework_TestCase
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
		file_put_contents(dirname(__FILE__) . '/../testInputFiles/AdvancedUpdateType.datatype', 
																			"int myInt\n" .
																			"float myFloat\n".
																			"text myText\n" .
																			"datetime myDatetime\n" .
																			'"YetAnotherType" myReference' . "\n" .
																			'"ThirdType" ref' . "\n");
		
		file_put_contents(dirname(__FILE__) . '/../testInputFiles/YetAnotherType.datatype', 
																			"int yourInt\n");
		
		file_put_contents(dirname(__FILE__) . '/../testInputFiles/ThirdType.datatype', 
																			'"YetAnotherType" ref' . "\n");
	
		$rolemodel = new \Good\Rolemodel\Rolemodel();
		$model = $rolemodel->createDataModel(array('AdvancedUpdateType' => 
														dirname(__FILE__) . '/../testInputFiles/AdvancedUpdateType.datatype',
												   'YetAnotherType' => 
														dirname(__FILE__) . '/../testInputFiles/YetAnotherType.datatype',
													'ThirdType' =>
														dirname(__FILE__) . '/../testInputFiles/ThirdType.datatype'));

		$service = new \Good\Service\Service();
		$service->compile(array(new \Good\Manners\Modifier\Storable()), $model, dirname(__FILE__) . '/../generated/');
		
		require dirname(__FILE__) . '/../generated/BaseAdvancedUpdateType.datatype.php';
		require dirname(__FILE__) . '/../generated/BaseYetAnotherType.datatype.php';
		require dirname(__FILE__) . '/../generated/BaseThirdType.datatype.php';
		
		$service->requireClasses(array('AdvancedUpdateType', 'YetAnotherType', 'ThirdType'));
		
		$manners = new \Good\Manners\Manners();
		$manners->compileStore($model, dirname(__FILE__) . '/../generated/');
		require dirname(__FILE__) . '/../generated/Store.php';

		$memory = new \Good\Memory\Memory();
		$memory->compileSQLStore($model, dirname(__FILE__) . '/../generated/');
		require dirname(__FILE__) . '/../generated/SQLStore.php';
		
		require dirname(__FILE__) . '/../generated/AdvancedUpdateTypeResolver.php';
		require dirname(__FILE__) . '/../generated/YetAnotherTypeResolver.php';
		require dirname(__FILE__) . '/../generated/ThirdTypeResolver.php';
	}
	
	public static function _tearDownAfterClass()
	{
		unlink(dirname(__FILE__) . '/../testInputFiles/AdvancedUpdateType.datatype');
		unlink(dirname(__FILE__) . '/../testInputFiles/YetAnotherType.datatype');
		unlink(dirname(__FILE__) . '/../testInputFiles/ThirdType.datatype');
		unlink(dirname(__FILE__) . '/../generated/BaseAdvancedUpdateType.datatype.php');
		unlink(dirname(__FILE__) . '/../generated/BaseYetAnotherType.datatype.php');
		unlink(dirname(__FILE__) . '/../generated/BaseThirdType.datatype.php');
		unlink(dirname(__FILE__) . '/../generated/AdvancedUpdateType.datatype.php');
		unlink(dirname(__FILE__) . '/../generated/YetAnotherType.datatype.php');
		unlink(dirname(__FILE__) . '/../generated/ThirdType.datatype.php');
		unlink(dirname(__FILE__) . '/../generated/AdvancedUpdateTypeCollection.php');
		unlink(dirname(__FILE__) . '/../generated/YetAnotherTypeCollection.php');
		unlink(dirname(__FILE__) . '/../generated/ThirdTypeCollection.php');
		unlink(dirname(__FILE__) . '/../generated/AdvancedUpdateTypeResolver.php');
		unlink(dirname(__FILE__) . '/../generated/YetAnotherTypeResolver.php');
		unlink(dirname(__FILE__) . '/../generated/ThirdTypeResolver.php');
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
		$db->query('TRUNCATE advancedupdatetype');
		$db->query('TRUNCATE yetanothertype');
		$db->query('TRUNCATE thirdtype');
		
		$store = $this->getNewStore();
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyReference(null);
		$ins->setRef(null);
		$store->insertAdvancedUpdateType($ins);
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new YetAnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$store->insertAdvancedUpdateType($ins);
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new YetAnotherType();
		$ref->setYourInt(30);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$store->insertAdvancedUpdateType($ins);
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ref = new YetAnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$store->insertAdvancedUpdateType($ins);
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$store->insertAdvancedUpdateType($ins);
		
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
		$db->query('TRUNCATE adavancedupdatetype');
		$db->query('TRUNCATE yetanothertype');
		$db->query('TRUNCATE thirdtype');
		
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
				  $hay->getMyReference()->getYourInt() == $needle->getMyReference()->getYourInt())) &&
				// they are both null
				(($hay->getRef() === null && $needle->getRef() === null) ||
				// or neither is null (so we won't be calling functions on null)
				// and they are the same
				 ($hay->getRef() !== null && $needle->getRef() !== null &&
				  $hay->getRef()->getRef()->getYourInt() == $needle->getRef()->getRef()->getYourInt())))
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
			/* 
			ob_start();
			echo "myInt: ";
			var_dump($needle->getMyInt());
			echo "myFloat: ";
			var_dump($needle->getMyFloat());
			echo "myText: ";
			var_dump($needle->getMyText());
			echo "myDatetime: ";
			var_dump($needle->getMyDatetime());
			echo "myReference->yourInt: ";
			var_dump($needle->getMyReference()->getYourInt());
			if ($needle->getRef() === null)
			{
				echo "ref: NULL\n";
			}
			else
			{
				echo "ref->ref->yourInt: ";
				var_dump($needle->getRef()->getRef()->getYourInt());
			}
			$out = ob_get_clean();
			throw new Exception("Failed asserting that an array contained an object with these properties: $out");
			*/
			// We're always wrong here.
			//$this->assertTrue(false, $haystack, "Failed asserting that an array contained an object with these properties: $out");
			
			// I'd rather not have huge messages when running my entire test suite.
			$this->assertTrue(false);
		}
		
		// To keep the assert count to what it actually is:
		$this->assertTrue(true);
		
		return $pos;
	}
	
	private function checkResults($expected)
	{
		// At the moment we don't have a proper api to get any,
		// but this trick does do the same
		$type = new AdvancedUpdateType();
		$any = $this->store2->createGreaterCondition($type);
		
		$resolver = new AdvancedUpdateTypeResolver();
		$resolver->resolveMyReference();
		$resolver->resolveRef()->resolveRef();
		
		$collection = $this->store2->getAdvancedUpdateTypeCollection($any, $resolver);
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expected);
			
			array_splice($expected, $pos, 1);
		}
		
		$this->assertSame(array(), $expected);		
	}
	
	public function testAdvancedUpdate()
	{
		// At the moment we don't have a proper api to get any,
		// but this trick does do the same
		$type = new AdvancedUpdateType();
		$any = $this->store1->createGreaterCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyInt(55);
		$modifications->setMyFloat(55.55);
		$modifications->setMyText("Fifty-five");
		$modifications->setMyDatetime(new Datetime("2055-05-05"));
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$expectedResults = array();
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(55);
		$ins->setMyFloat(55.55);
		$ins->setMyText("Fifty-five");
		$ins->setMyDatetime(new Datetime("2055-05-05"));
		$ins->setMyReference(null);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(55);
		$ins->setMyFloat(55.55);
		$ins->setMyText("Fifty-five");
		$ins->setMyDatetime(new Datetime("2055-05-05"));
		$ref = new YetAnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(55);
		$ins->setMyFloat(55.55);
		$ins->setMyText("Fifty-five");
		$ins->setMyDatetime(new Datetime("2055-05-05"));
		$ref = new YetAnotherType();
		$ref->setYourInt(30);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(55);
		$ins->setMyFloat(55.55);
		$ins->setMyText("Fifty-five");
		$ins->setMyDatetime(new Datetime("2055-05-05"));
		$ref = new YetAnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(55);
		$ins->setMyFloat(55.55);
		$ins->setMyText("Fifty-five");
		$ins->setMyDatetime(new Datetime("2055-05-05"));
		$ref = new YetAnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
	
	public function testAdavancedUpdateSetToNull()
	{
		// At the moment we don't have a proper api to get any,
		// but this trick does do the same
		$type = new AdvancedUpdateType();
		$type->setMyInt(5);
		$greater = $this->store1->createGreaterCondition($type);
		
		$type = new AdvancedUpdateType();
		$type->setMyFloat(20.0);
		$less = $this->store1->createLessCondition($type);
		
		$and = $this->store1->createAndCondition($greater, $less);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyInt(null);
		$modifications->setMyFloat(null);
		$modifications->setMyText(null);
		$modifications->setMyDatetime(null);
		
		$this->store1->modifyAnyAdvancedUpdateType($and, $modifications);
		
		$expectedResults = array();
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyReference(null);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new YetAnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(null);
		$ins->setMyText(null);
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(30);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(null);
		$ins->setMyText(null);
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
	
	public function testAdvancedUpdateSetReferenceToNull()
	{
		$type = new AdvancedUpdateType();
		$type->setMyInt(8);
		$any = $this->store1->createEqualityCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyReference(null);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$expectedResults = array();
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyReference(null);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new YetAnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ins->setMyReference(null);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ref = new YetAnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
	
	public function testAdvancedUpdateSetReferenceToExistingObject()
	{
		// first, we're fetching the object
		$type = new AdvancedUpdateType();
		$type->setMyInt(10);
		$any = $this->store1->createEqualityCondition($type);
		
		$resolver = new AdvancedUpdateTypeResolver();
		$resolver->resolveMyReference();
		$collection = $this->store1->getAdvancedUpdateTypeCollection($any, $resolver);
		
		$type= $collection->getNext();
		$ref = $type->getMyReference();
		
		$type = new AdvancedUpdateType();
		$type->setMyInt(8);
		$any = $this->store1->createLessCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyReference($ref);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$expectedResults = array();
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ref = new YetAnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new YetAnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new YetAnotherType();
		$ref->setYourInt(30);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ref = new YetAnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
		
		// Check the object wasn't duplicated
		
		$type = new YetAnotherType();
		$type->setYourInt(20);
		$cond = $this->store2->createEqualityCondition($type);
		
		$collection = $this->store2->getYetAnotherTypeCollection($cond, new YetAnotherTypeResolver());
		
		$collection->getNext();
		
		$this->assertSame(null, $collection->getNext());
	}
	
	public function testAdvancedUpdateSetReferenceToNewObject()
	{
		$type = new AdvancedUpdateType();
		$type->setMyInt(8);
		$any = $this->store1->createEqualityCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$ref = new YetAnotherType();
		$ref->setYourInt(144);
		$modifications->setMyReference($ref);
		$this->store1->insertYetAnotherType($ref);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$expectedResults = array();
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyReference(null);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new YetAnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new YetAnotherType();
		$ref->setYourInt(144);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ref = new YetAnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
	
	public function testAdvancedUpdateSetPropertyOfReference()
	{
		$type = new AdvancedUpdateType();
		$any = $this->store1->createEqualityCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$ref = new YetAnotherType();
		$ref->setYourInt(42);
		$modifications->setMyReference($ref);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$expectedResults = array();
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyReference(null);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new YetAnotherType();
		$ref->setYourInt(42);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new YetAnotherType();
		$ref->setYourInt(42);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ref = new YetAnotherType();
		$ref->setYourInt(42);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(42);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
		
		// Check there are still 4 separate YetAnotherType objects
		
		$type = new YetAnotherType();
		$cond = $this->store2->createEqualityCondition($type);
		
		$collection = $this->store2->getYetAnotherTypeCollection($cond, new YetAnotherTypeResolver());
		
		$i = 0;
		
		while ($collection->getNext())
		{
			$i++;
		}
		
		$this->assertSame(4, $i);
	}
	
	public function testAdvancedUpdateComparisons()
	{
		// Equality
		$type = new AdvancedUpdateType();
		$type->setMyInt(4);
		$any = $this->store1->createEqualityCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyInt(1);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		// Inequality
		$type = new AdvancedUpdateType();
		$type->setMyInt(1);
		$any = $this->store1->createInequalityCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyText("Hello World!");
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		// Less
		$type = new AdvancedUpdateType();
		$type->setMyInt(5);
		$any = $this->store1->createLessCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyText("Goodbye");
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		// LessOrEquals
		$type = new AdvancedUpdateType();
		$type->setMyInt(5);
		$any = $this->store1->createLessOrEqualsCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyFloat(47.47);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		// Greater
		$type = new AdvancedUpdateType();
		$type->setMyInt(8);
		$any = $this->store1->createGreaterCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyFloat(11.11);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		// GreaterOrEquals
		$type = new AdvancedUpdateType();
		$type->setMyInt(8);
		$any = $this->store1->createGreaterOrEqualsCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyDatetime(new Datetime('1989-04-11'));
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$expectedResults = array();
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(1);
		$ins->setMyFloat(47.47);
		$ins->setMyText("Goodbye");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyReference(null);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(5);
		$ins->setMyFloat(47.47);
		$ins->setMyText("Hello World!");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new YetAnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Hello World!");
		$ins->setMyDatetime(new Datetime('1989-04-11'));
		$ref = new YetAnotherType();
		$ref->setYourInt(30);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(10);
		$ins->setMyFloat(11.11);
		$ins->setMyText("Hello World!");
		$ins->setMyDatetime(new Datetime('1989-04-11'));
		$ref = new YetAnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
	
	public function testAdvancedUpdateAndOr()
	{
		$type = new AdvancedUpdateType();
		$type->setMyInt(10);
		$less = $this->store1->createLessCondition($type);
		
		$type = new AdvancedUpdateType();
		$type->setMyInt(4);
		$greater = $this->store1->createGreaterCondition($type);
		
		$any = $this->store1->createAndCondition($less, $greater);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyFloat(66.67);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$type = new AdvancedUpdateType();
		$type->setMyInt(5);
		$less = $this->store1->createLessCondition($type);
		
		$type = new AdvancedUpdateType();
		$type->setMyInt(8);
		$greater = $this->store1->createGreaterCondition($type);
		
		$any = $this->store1->createOrCondition($less, $greater);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyText("My oh My");
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$expectedResults = array();
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("My oh My");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyReference(null);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(5);
		$ins->setMyFloat(66.67);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new YetAnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(8);
		$ins->setMyFloat(66.67);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new YetAnotherType();
		$ref->setYourInt(30);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("My oh My");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ref = new YetAnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
	
	public function testAdvancedUpdateMultipleInOneCondition()
	{
		$type = new AdvancedUpdateType();
		$type->setMyInt(5);
		$type->setMyReference(new YetAnotherType());
		$type->getMyReference()->setYourInt(10);
		
		$any = $this->store1->createGreaterCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyText("Something else");
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$expectedResults = array();
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyReference(null);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new YetAnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Something else");
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new YetAnotherType();
		$ref->setYourInt(30);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Something else");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ref = new YetAnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
	
	public function testAdvancedUpdateByDate()
	{
		$type = new AdvancedUpdateType();
		$type->setMyDatetime(new Datetime('2005-05-05'));
		
		$any = $this->store1->createGreaterCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyInt(-1);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$expectedResults = array();
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyReference(null);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new YetAnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(-1);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new YetAnotherType();
		$ref->setYourInt(30);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(-1);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ref = new YetAnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
	
	public function testAdvancedUpdateByText()
	{
		$type = new AdvancedUpdateType();
		$type->setMyText("Four");
		
		$any = $this->store1->createEqualityCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyInt(455);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$expectedResults = array();
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(455);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyReference(null);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new YetAnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new YetAnotherType();
		$ref->setYourInt(30);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ref = new YetAnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
	
	public function testAdvancedUpdateEqualsNull()
	{
		$type = new AdvancedUpdateType();
		$type->setMyInt(null);
		
		$any = $this->store1->createEqualityCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyFloat(666.666);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$type = new AdvancedUpdateType();
		$type->setMyFloat(null);
		
		$any = $this->store1->createEqualityCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyInt(666);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$type = new AdvancedUpdateType();
		$type->setMyText(null);
		
		$any = $this->store1->createEqualityCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyDatetime(new Datetime('2066-06-06'));
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$type = new AdvancedUpdateType();
		$type->setMyDatetime(null);
		
		$any = $this->store1->createEqualityCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyText("Six Six Six");
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$type = new AdvancedUpdateType();
		$type->setMyReference(null);
		
		$any = $this->store1->createEqualityCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyInt(777);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$expectedResults = array();
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(777);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyReference(null);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(666);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new YetAnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2066-06-06'));
		$ref = new YetAnotherType();
		$ref->setYourInt(30);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ref = new YetAnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(666.666);
		$ins->setMyText("Six Six Six");
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
	
	public function testAdvancedUpdateDoesNotEqualNullIntAndText()
	{
		$type = new AdvancedUpdateType();
		$type->setMyInt(null);
		
		$any = $this->store1->createInequalityCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyFloat(666.666);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$type = new AdvancedUpdateType();
		$type->setMyText(null);
		
		$any = $this->store1->createInequalityCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyDatetime(new Datetime('2066-06-06'));
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$expectedResults = array();
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(4);
		$ins->setMyFloat(666.666);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2066-06-06'));
		$ins->setMyReference(null);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(5);
		$ins->setMyFloat(666.666);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2066-06-06'));
		$ref = new YetAnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(8);
		$ins->setMyFloat(666.666);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new YetAnotherType();
		$ref->setYourInt(30);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(10);
		$ins->setMyFloat(666.666);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2066-06-06'));
		$ref = new YetAnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(new \Datetime('2066-06-06'));
		$ref = new YetAnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
	
	public function testAdvancedUpdateDoesNotEqualNullFloatAndDate()
	{
		$type = new AdvancedUpdateType();
		$type->setMyFloat(null);
		
		$any = $this->store1->createInequalityCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyInt(666);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$type = new AdvancedUpdateType();
		$type->setMyDatetime(null);
		
		$any = $this->store1->createInequalityCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyText("Six Six Six");
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$expectedResults = array();
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(666);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Six Six Six");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyReference(null);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Six Six Six");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new YetAnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(666);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Six Six Six");
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new YetAnotherType();
		$ref->setYourInt(30);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(666);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Six Six Six");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ref = new YetAnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(666);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
	
	public function testAdvancedUpdateDoesNotEqualNullReference()
	{
		$type = new AdvancedUpdateType();
		$type->setMyReference(null);
		
		$any = $this->store1->createInequalityCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyInt(777);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$expectedResults = array();
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyReference(null);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(777);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new YetAnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(777);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new YetAnotherType();
		$ref->setYourInt(30);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(777);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ref = new YetAnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(777);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
	
	public function testAdvancedUpdateByExistingReference()
	{
		// Let's firt mess up the data a little, so we can test
		// it changes exactly as much as it should
		$type = new YetAnotherType();
		$any = $this->store1->createEqualityCondition($type);
		$modifications = new YetAnotherType();
		$modifications->setYourInt(42);
		$this->store1->modifyAnyYetAnotherType($any, $modifications);
		// one more change:
		$type = new AdvancedUpdateType();
		$type->setMyInt(8);
		$any = $this->store1->createEqualityCondition($type);
		$resolver = new AdvancedUpdateTypeResolver();
		$resolver->resolveMyReference();
		$collection = $this->store1->getAdvancedUpdateTypeCollection($any, $resolver);
		$ref = $collection->getNext()->getMyReference();
		$type = new AdvancedUpdateType();
		$type->setMyInt(10);
		$any = $this->store1->createEqualityCondition($type);
		$modifications = new AdvancedUpdateType();
		$modifications->setMyReference($ref);
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		// Now we get our reference
		// (it's already in $ref, but it would be mixing of concerns if we relied on that)
		$type = new AdvancedUpdateType();
		$type->setMyInt(10);
		$any = $this->store1->createEqualityCondition($type);
		$resolver = new AdvancedUpdateTypeResolver();
		$resolver->resolveMyReference();
		$collection = $this->store1->getAdvancedUpdateTypeCollection($any, $resolver);
		$ref = $collection->getNext()->getMyReference();
		
		// And now we can finally do the real test
		$type = new AdvancedUpdateType();
		$type->setMyReference($ref);
		
		$any = $this->store1->createEqualityCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyInt(1);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$expectedResults = array();
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyReference(null);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new YetAnotherType();
		$ref->setYourInt(42);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(1);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new YetAnotherType();
		$ref->setYourInt(42);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(1);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ref = new YetAnotherType();
		$ref->setYourInt(42);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(42);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
	
	public function testAdvancedUpdateObjectAndReference()
	{
		$type = new AdvancedUpdateType();
		$type->setMyInt(5);
		
		$cond1 = $this->store1->createGreaterCondition($type);
		
		$type = new AdvancedUpdateType();
		$type->setMyReference(new YetAnotherType());
		$type->getMyReference()->setYourInt(10);
		
		$cond2 = $this->store1->createGreaterCondition($type);
		
		$any = $this->store1->createAndCondition($cond1, $cond2);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyInt(100);
		$modifications->setMyReference(new YetAnotherType());
		$modifications->getMyReference()->setYourInt(100);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$expectedResults = array();
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyReference(null);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new YetAnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(100);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new YetAnotherType();
		$ref->setYourInt(100);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(100);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ref = new YetAnotherType();
		$ref->setYourInt(100);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
	
	public function testAdvancedUpdateObjectDeepReference()
	{
		// First insert a couple ThirdTypes into our dataset
		$type = new AdvancedUpdateType();
		$any = $this->store1->createEqualityCondition($type);
		$resolver = new AdvancedUpdateTypeResolver();
		$resolver->resolveRef();
		$collection = $this->store1->getAdvancedUpdateTypeCollection($any, $resolver);
		
		while ($type = $collection->getNext())
		{
			if ($type->getMyInt() == 4)
			{
				$ref = new ThirdType();
				$refref = new YetAnotherType();
				$refref->setYourInt(500);
				$ref->setRef($refref);
				$this->store1->insertThirdType($ref);
				$type->setRef($ref);
			}
			else if ($type->getMyInt() == 5)
			{
				$ref = new ThirdType();
				$refref = new YetAnotherType();
				$refref->setYourInt(300);
				$ref->setRef($refref);
				$this->store1->insertThirdType($ref);
				$type->setRef($ref);
			}
			else if ($type->getMyInt() == 8)
			{
				$ref = new ThirdType();
				$refref = new YetAnotherType();
				$refref->setYourInt(400);
				$ref->setRef($refref);
				$this->store1->insertThirdType($ref);
				$type->setRef($ref);
			}
			else if ($type->getMyInt() == 10)
			{
				$ref = new ThirdType();
				$refref = new YetAnotherType();
				$refref->setYourInt(200);
				$ref->setRef($refref);
				$this->store1->insertThirdType($ref);
				$type->setRef($ref);
			}
		}
		
		$type = new AdvancedUpdateType();
		$type->setRef(new ThirdType());
		$type->getRef()->setRef(new YetAnotherType());
		$type->getRef()->getRef()->setYourInt(300);
		
		$any = $this->store1->createGreaterCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyInt(99999);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$expectedResults = array();
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(99999);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyReference(null);
		$ins->setRef(new ThirdType());
		$ins->getRef()->setRef(new YetAnotherType());
		$ins->getRef()->getRef()->setYourInt(500);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new YetAnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$ins->setRef(new ThirdType());
		$ins->getRef()->setRef(new YetAnotherType());
		$ins->getRef()->getRef()->setYourInt(300);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(99999);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new YetAnotherType();
		$ref->setYourInt(30);
		$ins->setMyReference($ref);
		$ins->setRef(new ThirdType());
		$ins->getRef()->setRef(new YetAnotherType());
		$ins->getRef()->getRef()->setYourInt(400);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ref = new YetAnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$ins->setRef(new ThirdType());
		$ins->getRef()->setRef(new YetAnotherType());
		$ins->getRef()->getRef()->setYourInt(200);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
	
	public function testAdvancedUpdateObjectDeepReferenceInverse()
	{
		// First insert a couple ThirdTypes into our dataset
		$type = new AdvancedUpdateType();
		$any = $this->store1->createEqualityCondition($type);
		$resolver = new AdvancedUpdateTypeResolver();
		$resolver->resolveRef();
		$collection = $this->store1->getAdvancedUpdateTypeCollection($any, $resolver);
		
		while ($type = $collection->getNext())
		{
			if ($type->getMyInt() == 4)
			{
				$ref = new ThirdType();
				$refref = new YetAnotherType();
				$refref->setYourInt(500);
				$ref->setRef($refref);
				$this->store1->insertThirdType($ref);
				$type->setRef($ref);
			}
			else if ($type->getMyInt() == 5)
			{
				$ref = new ThirdType();
				$refref = new YetAnotherType();
				$refref->setYourInt(300);
				$ref->setRef($refref);
				$this->store1->insertThirdType($ref);
				$type->setRef($ref);
			}
			else if ($type->getMyInt() == 8)
			{
				$ref = new ThirdType();
				$refref = new YetAnotherType();
				$refref->setYourInt(400);
				$ref->setRef($refref);
				$this->store1->insertThirdType($ref);
				$type->setRef($ref);
			}
			else if ($type->getMyInt() == 10)
			{
				$ref = new ThirdType();
				$refref = new YetAnotherType();
				$refref->setYourInt(200);
				$ref->setRef($refref);
				$this->store1->insertThirdType($ref);
				$type->setRef($ref);
			}
		}
		
		$type = new AdvancedUpdateType();
		$type->setMyInt(5);
		
		$any = $this->store1->createGreaterCondition($type);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setRef(new ThirdType());
		$modifications->getRef()->setRef(new YetAnotherType());
		$modifications->getRef()->getRef()->setYourInt(666);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$expectedResults = array();
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyReference(null);
		$ins->setRef(new ThirdType());
		$ins->getRef()->setRef(new YetAnotherType());
		$ins->getRef()->getRef()->setYourInt(500);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new YetAnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$ins->setRef(new ThirdType());
		$ins->getRef()->setRef(new YetAnotherType());
		$ins->getRef()->getRef()->setYourInt(300);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(8);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new YetAnotherType();
		$ref->setYourInt(30);
		$ins->setMyReference($ref);
		$ins->setRef(new ThirdType());
		$ins->getRef()->setRef(new YetAnotherType());
		$ins->getRef()->getRef()->setYourInt(666);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(10);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ref = new YetAnotherType();
		$ref->setYourInt(20);
		$ins->setMyReference($ref);
		$ins->setRef(new ThirdType());
		$ins->getRef()->setRef(new YetAnotherType());
		$ins->getRef()->getRef()->setYourInt(666);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
	
	public function testCircularUpdateAndConditionBug()
	{
		// issue #38
		$type = new AdvancedUpdateType();
		$type->setMyInt(5);
		
		$cond1 = $this->store1->createGreaterCondition($type);
		
		$type = new AdvancedUpdateType();
		$type->setMyReference(new YetAnotherType());
		$type->getMyReference()->setYourInt(10);
		
		$cond2 = $this->store1->createGreaterCondition($type);
		
		$any = $this->store1->createAndCondition($cond1, $cond2);
		
		$modifications = new AdvancedUpdateType();
		$modifications->setMyInt(1);
		$modifications->setMyReference(new YetAnotherType());
		$modifications->getMyReference()->setYourInt(1);
		
		$this->store1->modifyAnyAdvancedUpdateType($any, $modifications);
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyReference(null);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(5);
		$ins->setMyFloat(null);
		$ins->setMyText("Five");
		$ins->setMyDatetime(new \Datetime('2005-05-05'));
		$ref = new YetAnotherType();
		$ref->setYourInt(40);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(1);
		$ins->setMyFloat(10.10);
		$ins->setMyText(null);
		$ins->setMyDatetime(new \Datetime('2008-08-08'));
		$ref = new YetAnotherType();
		$ref->setYourInt(1);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(1);
		$ins->setMyFloat(10.10);
		$ins->setMyText("Ten");
		$ins->setMyDatetime(new \Datetime('2010-10-10'));
		$ref = new YetAnotherType();
		$ref->setYourInt(1);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$ins = new AdvancedUpdateType();
		$ins->setMyInt(null);
		$ins->setMyFloat(20.20);
		$ins->setMyText("Twenty");
		$ins->setMyDatetime(null);
		$ref = new YetAnotherType();
		$ref->setYourInt(10);
		$ins->setMyReference($ref);
		$ins->setRef(null);
		$expectedResults[] = $ins;
		
		$this->checkResults($expectedResults);
	}
}

?>