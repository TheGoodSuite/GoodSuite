<?php

/** 
 * @runTestsInSeparateProcesses
 */
abstract class GoodMannersInsertTest extends PHPUnit_Framework_TestCase
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
		file_put_contents(dirname(__FILE__) . '/../testInputFiles/InsertType.datatype', 
																			"int myInt\n" .
																			"float myFloat\n".
																			"text myText\n" .
																			"datetime myDatetime\n" .
																			'"InsertType" myCircularReference' . "\n");
	
		$rolemodel = new \Good\Rolemodel\Rolemodel();
		$model = $rolemodel->createDataModel(array('InsertType' => dirname(__FILE__) . '/../testInputFiles/InsertType.datatype'));

		$service = new \Good\Service\Service();
		$service->compile(array(new \Good\Manners\Modifier\Storable()), $model, dirname(__FILE__) . '/../generated/');
		
		require dirname(__FILE__) . '/../generated/BaseInsertType.datatype.php';
		
		$service->requireClasses(array('InsertType'));
		
		$manners = new \Good\Manners\Manners();
		$manners->compileStore($model, dirname(__FILE__) . '/../generated/');
		require dirname(__FILE__) . '/../generated/Store.php';

		$memory = new \Good\Memory\Memory();
		$memory->compileSQLStore($model, dirname(__FILE__) . '/../generated/');
		require dirname(__FILE__) . '/../generated/SQLStore.php';
		
		require dirname(__FILE__) . '/../generated/InsertTypeResolver.php';
	}
	
	public static function _tearDownAfterClass()
	{
		unlink(dirname(__FILE__) . '/../testInputFiles/InsertType.datatype');
		unlink(dirname(__FILE__) . '/../generated/BaseInsertType.datatype.php');
		unlink(dirname(__FILE__) . '/../generated/InsertType.datatype.php');
		unlink(dirname(__FILE__) . '/../generated/InsertTypeCollection.php');
		unlink(dirname(__FILE__) . '/../generated/InsertTypeResolver.php');
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
		$db->query('TRUNCATE inserttype');
		
		// two stores, so communication will have to go through data storage
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
		$db->query('TRUNCATE inserttype');
		
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
				($hay->getMyCircularReference() === null && $needle->getMyCircularReference() === null) ||
				// or neither is null (so we won't be calling functions on null)
				// and they are the same
				 ($hay->getMyCircularReference() !== null && $needle->getMyCircularReference() !== null &&
				  $hay->getMyCircularReference()->getMyInt() == $needle->getMyCircularReference()->getMyInt() &&
				  $hay->getMyCircularReference()->getMyFloat() == $needle->getMyCircularReference()->getMyFloat() &&
				  $hay->getMyCircularReference()->getMyText() == $needle->getMyCircularReference()->getMyText() &&
				  $hay->getMyCircularReference()->getMyDatetime() == $needle->getMyCircularReference()->getMyDatetime()))
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
	
	private function checkInsertion($expected)
	{
		// At the moment we don't have a proper api to get any,
		// but this trick does do the same
		$type = new InsertType();
		$any = $this->store2->createGreaterCondition($type);
		
		$resolver = new InsertTypeResolver();
		$resolver->resolveMyCircularReference();
		$collection = $this->store2->getInsertTypeCollection($any, $resolver);
		
		while ($type = $collection->getNext())
		{
			$pos = $this->assertContainsAndReturnIndex_specific($type, $expected);
			
			array_splice($expected, $pos, 1);
		}
		
		$this->assertSame(array(), $expected);		
	}
	
	public function testBasicInsertion()
	{
		$ins = new InsertType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyCircularReference(null);
		$this->store1->insertInsertType($ins);
		
		$expectedResults = array();
		
		// we create another copy, so we can't be influenced by
		// the store changing the object
		$ins = new InsertType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		$ins->setMyCircularReference(null);
		$expectedResults[] = $ins;
		
		$this->store1->flush();
		
		$this->checkInsertion($expectedResults);
	}
	
	public function testCircularReferenceInsertion()
	{
		$ins = new InsertType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		
		$ins2 = new InsertType();
		$ins2->setMyInt(7);
		$ins2->setMyFloat(7.7);
		$ins2->setMyText("Seven");
		$ins2->setMyDatetime(new \Datetime('2007-07-07'));
		$ins2->setMyCircularReference($ins);
		
		$ins->setMyCircularReference($ins2);
		
		$this->store1->insertInsertType($ins);
		$this->store1->insertInsertType($ins2);
		
		$expectedResults = array();
		
		// we create another copy, so we can't be influenced by
		// the store changing the object
		$ins = new InsertType();
		$ins->setMyInt(4);
		$ins->setMyFloat(4.4);
		$ins->setMyText("Four");
		$ins->setMyDatetime(new \Datetime('2004-04-04'));
		
		$ins2 = new InsertType();
		$ins2->setMyInt(7);
		$ins2->setMyFloat(7.7);
		$ins2->setMyText("Seven");
		$ins2->setMyDatetime(new \Datetime('2007-07-07'));
		$ins2->setMyCircularReference($ins);
		
		$ins->setMyCircularReference($ins2);
		
		$expectedResults[] = $ins;
		$expectedResults[] = $ins2;
		
		$this->store1->flush();
		
		$this->checkInsertion($expectedResults);
	}
	
	public function testCircularNullsInsertion()
	{
		$ins = new InsertType();
		$ins->setMyInt(null);
		$ins->setMyFloat(null);
		$ins->setMyText(null);
		$ins->setMyDatetime(null);
		$ins->setMyCircularReference(null);
		$this->store1->insertInsertType($ins);
		
		$expectedResults = array();
		
		// we create another copy, so we can't be influenced by
		// the store changing the object
		$ins = new InsertType();
		$ins->setMyInt(null);
		$ins->setMyFloat(null);
		$ins->setMyText(null);
		$ins->setMyDatetime(null);
		$ins->setMyCircularReference(null);
		$expectedResults[] = $ins;
		
		$this->store1->flush();
		
		$this->checkInsertion($expectedResults);
	}
}

?>