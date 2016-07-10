<?php

/** 
 * @runTestsInSeparateProcesses
 */
abstract class GoodMannersAdvancedUpdateTest extends PHPUnit_Framework_TestCase
{
    private $storage1;
    private $storage2;
    
    abstract public function getNewStorage();
    // this function should be removed, but is used for clearing the database at the moment
    abstract public function getNewDb();
    abstract public function truncateTable($table);
    
    // This could be done just once for all the tests and it would even be necessary
    // to run the tests in this class in a single process.
    // However, since we can't run these tests in the same process as those from other
    // classes (we would have namespace collisions for Storage and SQLStorage)
    // we have to run every test in different class, and setUpBeforeClass doesn't
    // play well with that. As such, we'll have to call this function from
    // setUp instead of having PHPUnit do its magic.
    public static function _setUpBeforeClass()
    {
        // Garbage collector causes segmentation fault, so we disable 
        // for the duration of the test case
        gc_disable();
        file_put_contents(dirname(__FILE__) . '/../testInputFiles/AdvancedUpdateType.datatype', 
                                                                            "datatype AdvancedUpdateType\n" .
                                                                            "{\n" .
                                                                            "   int myInt;\n" .
                                                                            "   float myFloat;\n".
                                                                            "   text myText;\n" .
                                                                            "   datetime myDatetime;\n" .
                                                                            '   "YetAnotherType" myReference;' . "\n" .
                                                                            '   "ThirdType" ref;' . "\n" .
                                                                            "}\n");
        
        file_put_contents(dirname(__FILE__) . '/../testInputFiles/YetAnotherType.datatype', 
                                                                            "datatype YetAnotherType { int yourInt; }");
        
        file_put_contents(dirname(__FILE__) . '/../testInputFiles/ThirdType.datatype', 
                                                                            'datatype ThirdType {"YetAnotherType" ref; }');
    
        $rolemodel = new \Good\Rolemodel\Rolemodel();
        $schema = $rolemodel->createSchema(array(dirname(__FILE__) . '/../testInputFiles/AdvancedUpdateType.datatype',
                                                 dirname(__FILE__) . '/../testInputFiles/YetAnotherType.datatype',
                                                 dirname(__FILE__) . '/../testInputFiles/ThirdType.datatype'));

        $service = new \Good\Service\Service();
        $service->compile(array(new \Good\Manners\Modifier\Storable()), $schema, dirname(__FILE__) . '/../generated/');
        
        require dirname(__FILE__) . '/../generated/AdvancedUpdateType.datatype.php';
        require dirname(__FILE__) . '/../generated/YetAnotherType.datatype.php';
        require dirname(__FILE__) . '/../generated/ThirdType.datatype.php';
        
        require dirname(__FILE__) . '/../generated/AdvancedUpdateTypeResolver.php';
        require dirname(__FILE__) . '/../generated/YetAnotherTypeResolver.php';
        require dirname(__FILE__) . '/../generated/ThirdTypeResolver.php';
    }
    
    public static function _tearDownAfterClass()
    {
        unlink(dirname(__FILE__) . '/../testInputFiles/AdvancedUpdateType.datatype');
        unlink(dirname(__FILE__) . '/../testInputFiles/YetAnotherType.datatype');
        unlink(dirname(__FILE__) . '/../testInputFiles/ThirdType.datatype');
        unlink(dirname(__FILE__) . '/../generated/AdvancedUpdateType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/YetAnotherType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/ThirdType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/AdvancedUpdateTypeResolver.php');
        unlink(dirname(__FILE__) . '/../generated/YetAnotherTypeResolver.php');
        unlink(dirname(__FILE__) . '/../generated/ThirdTypeResolver.php');
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
        $this->truncateTable('advancedupdatetype');
        $this->truncateTable('yetanothertype');
        $this->truncateTable('thirdtype');
        
        $storage = $this->getNewStorage();
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \Datetime('2004-04-04');
        $ins->myReference = null;
        $ins->ref = null;
        $storage->insert($ins);
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \Datetime('2005-05-05');
        $ref = new YetAnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $ins->ref = null;
        $storage->insert($ins);
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \Datetime('2008-08-08');
        $ref = new YetAnotherType();
        $ref->yourInt = 30;
        $ins->myReference = $ref;
        $ins->ref = null;
        $storage->insert($ins);
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \Datetime('2010-10-10');
        $ref = new YetAnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $ins->ref = null;
        $storage->insert($ins);
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $ins->ref = null;
        $storage->insert($ins);
        
        $storage->flush();
        
        // new Storage, so communication will have to go through data storage
        $this->storage1 = $this->getNewStorage();
        $this->storage2 = $this->getNewStorage();
    }
    
    public function tearDown()
    {
        // Just doing this already to make sure the deconstructor will hasve
        // side-effects at an unspecified moment...
        // (at which point the database will probably be in a wrong state for this)
        $this->storage1->flush();
        $this->storage2->flush();
        
        // this should be handled through the GoodManners API once that is implemented
        $this->truncateTable('advancedupdatetype');
        $this->truncateTable('yetanothertype');
        $this->truncateTable('thirdtype');
        
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
            if ($hay->myInt == $needle->myInt &&
                $hay->myFloat == $needle->myFloat &&
                $hay->myText == $needle->myText &&
                $hay->myDatetime == $needle->myDatetime &&
                // they are both null
                (($hay->myReference === null && $needle->myReference === null) ||
                // or neither is null (so we won't be calling functions on null)
                // and they are the same
                 ($hay->myReference !== null && $needle->myReference !== null &&
                  $hay->myReference->yourInt == $needle->myReference->yourInt)) &&
                // they are both null
                (($hay->ref === null && $needle->ref === null) ||
                // or neither is null (so we won't be calling functions on null)
                // and they are the same
                 ($hay->ref !== null && $needle->ref !== null &&
                  $hay->ref->ref->yourInt == $needle->ref->ref->yourInt)))
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
            var_dump($needle->myInt);
            echo "myFloat: ";
            var_dump($needle->myFloat);
            echo "myText: ";
            var_dump($needle->myText);
            echo "myDatetime: ";
            var_dump($needle->myDatetime);
            echo "myReference->yourInt: ";
            if ($needle->ref === null)
            {
                echo "myReference: NULL\n";
            }
            else
            {
                echo "myReference->yourInt: ";
                var_dump($needle->myReference->yourInt);
            }
            if ($needle->ref() === null)
            {
                echo "ref: NULL\n";
            }
            else
            {
                echo "ref->ref->yourInt: ";
                var_dump($needle->ref->ref->yourInt);
            }
            $out = ob_get_clean();
            
            throw new Exception("Failed asserting that an array contained an object with these properties: $out");
            
            // We're always wrong here.
            //$this->assertTrue(false, $haystack, "Failed asserting that an array contained an object with these properties: $out");
            */
            
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
        $any = new \Good\Manners\Condition\GreaterThan($type);
        
        $resolver = new AdvancedUpdateTypeResolver();
        $resolver->resolveMyReference();
        $resolver->resolveRef()->resolveRef();
        
        $collection = $this->storage2->getCollection($any, $resolver);
        
        foreach ($collection as $type)
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
        $any = new \Good\Manners\Condition\GreaterThan($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myInt = 55;
        $modifications->myFloat = 55.55;
        $modifications->myText = "Fifty-five";
        $modifications->myDatetime = new Datetime("2055-05-05");
        
        $this->storage1->modifyAny($any, $modifications);
        
        $expectedResults = array();
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 55;
        $ins->myFloat = 55.55;
        $ins->myText = "Fifty-five";
        $ins->myDatetime = new Datetime("2055-05-05");
        $ins->myReference = null;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 55;
        $ins->myFloat = 55.55;
        $ins->myText = "Fifty-five";
        $ins->myDatetime = new Datetime("2055-05-05");
        $ref = new YetAnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 55;
        $ins->myFloat = 55.55;
        $ins->myText = "Fifty-five";
        $ins->myDatetime = new Datetime("2055-05-05");
        $ref = new YetAnotherType();
        $ref->yourInt = 30;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 55;
        $ins->myFloat = 55.55;
        $ins->myText = "Fifty-five";
        $ins->myDatetime = new Datetime("2055-05-05");
        $ref = new YetAnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 55;
        $ins->myFloat = 55.55;
        $ins->myText = "Fifty-five";
        $ins->myDatetime = new Datetime("2055-05-05");
        $ref = new YetAnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
    }
    
    public function testAdavancedUpdateSetToNull()
    {
        // At the moment we don't have a proper api to get any,
        // but this trick does do the same
        $type = new AdvancedUpdateType();
        $type->myInt = 5;
        $greater = new \Good\Manners\Condition\GreaterThan($type);
        
        $type = new AdvancedUpdateType();
        $type->myFloat = 20.0;
        $less = new \Good\Manners\Condition\LessThan($type);
        
        $and = new \Good\Manners\Condition\AndCondition($greater, $less);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myInt = null;
        $modifications->myFloat = null;
        $modifications->myText = null;
        $modifications->myDatetime = null;
        
        $this->storage1->modifyAny($and, $modifications);
        
        $expectedResults = array();
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \Datetime('2004-04-04');
        $ins->myReference = null;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \Datetime('2005-05-05');
        $ref = new YetAnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = null;
        $ins->myFloat = null;
        $ins->myText = null;
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 30;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = null;
        $ins->myFloat = null;
        $ins->myText = null;
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
    }
    
    public function testAdvancedUpdateSetReferenceToNull()
    {
        $type = new AdvancedUpdateType();
        $type->myInt = 8;
        $any = new \Good\Manners\Condition\EqualTo($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myReference = null;
        
        $this->storage1->modifyAny($any, $modifications);
        
        $expectedResults = array();
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \Datetime('2004-04-04');
        $ins->myReference = null;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \Datetime('2005-05-05');
        $ref = new YetAnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \Datetime('2008-08-08');
        $ins->myReference = null;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \Datetime('2010-10-10');
        $ref = new YetAnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
    }
    
    public function testAdvancedUpdateSetReferenceToExistingObject()
    {
        // first, we're fetching the object
        $type = new AdvancedUpdateType();
        $type->myInt = 10;
        $any = new \Good\Manners\Condition\EqualTo($type);
        
        $resolver = new AdvancedUpdateTypeResolver();
        $resolver->resolveMyReference();
        $collection = $this->storage1->getCollection($any, $resolver);
        
        $type= $collection->getNext();
        $ref = $type->myReference;
        
        $type = new AdvancedUpdateType();
        $type->myInt = 8;
        $any = new \Good\Manners\Condition\LessThan($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myReference = $ref;
        
        $this->storage1->modifyAny($any, $modifications);
        
        $expectedResults = array();
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \Datetime('2004-04-04');
        $ref = new YetAnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \Datetime('2005-05-05');
        $ref = new YetAnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \Datetime('2008-08-08');
        $ref = new YetAnotherType();
        $ref->yourInt = 30;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \Datetime('2010-10-10');
        $ref = new YetAnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
        
        // Check the object wasn't duplicated
        
        $type = new YetAnotherType();
        $type->yourInt = 20;
        $cond = new \Good\Manners\Condition\EqualTo($type);
        
        $collection = $this->storage2->getCollection($cond, new YetAnotherTypeResolver());
        
        $collection->getNext();
        
        $this->assertSame(null, $collection->getNext());
    }
    
    public function testAdvancedUpdateSetReferenceToNewObject()
    {
        $type = new AdvancedUpdateType();
        $type->myInt = 8;
        $any = new \Good\Manners\Condition\EqualTo($type);
        
        $modifications = new AdvancedUpdateType();
        $ref = new YetAnotherType();
        $ref->yourInt = 144;
        $modifications->myReference = $ref;
        $this->storage1->insert($ref);
        
        $this->storage1->modifyAny($any, $modifications);
        
        $expectedResults = array();
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \Datetime('2004-04-04');
        $ins->myReference = null;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \Datetime('2005-05-05');
        $ref = new YetAnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \Datetime('2008-08-08');
        $ref = new YetAnotherType();
        $ref->yourInt = 144;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \Datetime('2010-10-10');
        $ref = new YetAnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
    }
    
    public function testAdvancedUpdateSetPropertyOfReference()
    {
        $type = new AdvancedUpdateType();
        $any = new \Good\Manners\Condition\EqualTo($type);
        
        $modifications = new AdvancedUpdateType();
        $ref = new YetAnotherType();
        $ref->yourInt = 42;
        $modifications->myReference = $ref;
        
        $this->storage1->modifyAny($any, $modifications);
        
        $expectedResults = array();
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \Datetime('2004-04-04');
        $ins->myReference = null;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \Datetime('2005-05-05');
        $ref = new YetAnotherType();
        $ref->yourInt = 42;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \Datetime('2008-08-08');
        $ref = new YetAnotherType();
        $ref->yourInt = 42;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \Datetime('2010-10-10');
        $ref = new YetAnotherType();
        $ref->yourInt = 42;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 42;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
        
        // Check there are still 4 separate YetAnotherType objects
        
        $type = new YetAnotherType();
        $cond = new \Good\Manners\Condition\EqualTo($type);
        
        $collection = $this->storage2->getCollection($cond, new YetAnotherTypeResolver());
        
        $i = 0;
        
        foreach ($collection as $elem)
        {
            $i++;
        }
        
        $this->assertSame(4, $i);
    }
    
    public function testAdvancedUpdateComparisons()
    {
        // EqualTo
        $type = new AdvancedUpdateType();
        $type->myInt = 4;
        $any = new \Good\Manners\Condition\EqualTo($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myInt = 1;
        
        $this->storage1->modifyAny($any, $modifications);
        
        // NotEqualTo
        $type = new AdvancedUpdateType();
        $type->myInt = 1;
        $any = new \Good\Manners\Condition\NotEqualTo($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myText = "Hello World!";
        
        $this->storage1->modifyAny($any, $modifications);
        
        // LessThan
        $type = new AdvancedUpdateType();
        $type->myInt = 5;
        $any = new \Good\Manners\Condition\LessThan($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myText = "Goodbye";
        
        $this->storage1->modifyAny($any, $modifications);
        
        // LessOrEqual
        $type = new AdvancedUpdateType();
        $type->myInt = 5;
        $any = new \Good\Manners\Condition\LessOrEqual($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myFloat = 47.47;
        
        $this->storage1->modifyAny($any, $modifications);
        
        // GreaterThan
        $type = new AdvancedUpdateType();
        $type->myInt = 8;
        $any = new \Good\Manners\Condition\GreaterThan($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myFloat = 11.11;
        
        $this->storage1->modifyAny($any, $modifications);
        
        // GreaterOrEqual
        $type = new AdvancedUpdateType();
        $type->myInt = 8;
        $any = new \Good\Manners\Condition\GreaterOrEqual($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myDatetime = new Datetime('1989-04-11');
        
        $this->storage1->modifyAny($any, $modifications);
        
        $expectedResults = array();
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 1;
        $ins->myFloat = 47.47;
        $ins->myText = "Goodbye";
        $ins->myDatetime = new \Datetime('2004-04-04');
        $ins->myReference = null;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 5;
        $ins->myFloat = 47.47;
        $ins->myText = "Hello World!";
        $ins->myDatetime = new \Datetime('2005-05-05');
        $ref = new YetAnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = "Hello World!";
        $ins->myDatetime = new Datetime('1989-04-11');
        $ref = new YetAnotherType();
        $ref->yourInt = 30;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 10;
        $ins->myFloat = 11.11;
        $ins->myText = "Hello World!";
        $ins->myDatetime = new Datetime('1989-04-11');
        $ref = new YetAnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
    }
    
    public function testAdvancedUpdateAndOr()
    {
        $type = new AdvancedUpdateType();
        $type->myInt = 10;
        $less = new \Good\Manners\Condition\LessThan($type);
        
        $type = new AdvancedUpdateType();
        $type->myInt = 4;
        $greater = new \Good\Manners\Condition\GreaterThan($type);
        
        $any = new \Good\Manners\Condition\AndCondition($less, $greater);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myFloat = 66.67;
        
        $this->storage1->modifyAny($any, $modifications);
        
        $type = new AdvancedUpdateType();
        $type->myInt = 5;
        $less = new \Good\Manners\Condition\LessThan($type);
        
        $type = new AdvancedUpdateType();
        $type->myInt = 8;
        $greater = new \Good\Manners\Condition\GreaterThan($type);
        
        $any = new \Good\Manners\Condition\OrCondition($less, $greater);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myText = "My oh My";
        
        $this->storage1->modifyAny($any, $modifications);
        
        $expectedResults = array();
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "My oh My";
        $ins->myDatetime = new \Datetime('2004-04-04');
        $ins->myReference = null;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 5;
        $ins->myFloat = 66.67;
        $ins->myText = "Five";
        $ins->myDatetime = new \Datetime('2005-05-05');
        $ref = new YetAnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 8;
        $ins->myFloat = 66.67;
        $ins->myText = null;
        $ins->myDatetime = new \Datetime('2008-08-08');
        $ref = new YetAnotherType();
        $ref->yourInt = 30;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "My oh My";
        $ins->myDatetime = new \Datetime('2010-10-10');
        $ref = new YetAnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
    }
    
    public function testAdvancedUpdateMultipleInOneCondition()
    {
        $type = new AdvancedUpdateType();
        $type->myInt = 5;
        $type->myReference = new YetAnotherType();
        $type->myReference->yourInt = 10;
        
        $any = new \Good\Manners\Condition\GreaterThan($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myText = "Something else";
        
        $this->storage1->modifyAny($any, $modifications);
        
        $expectedResults = array();
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \Datetime('2004-04-04');
        $ins->myReference = null;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \Datetime('2005-05-05');
        $ref = new YetAnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = "Something else";
        $ins->myDatetime = new \Datetime('2008-08-08');
        $ref = new YetAnotherType();
        $ref->yourInt = 30;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Something else";
        $ins->myDatetime = new \Datetime('2010-10-10');
        $ref = new YetAnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
    }
    
    public function testAdvancedUpdateByDate()
    {
        $type = new AdvancedUpdateType();
        $type->myDatetime = new Datetime('2005-05-05');
        
        $any = new \Good\Manners\Condition\GreaterThan($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myInt = -1;
        
        $this->storage1->modifyAny($any, $modifications);
        
        $expectedResults = array();
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \Datetime('2004-04-04');
        $ins->myReference = null;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \Datetime('2005-05-05');
        $ref = new YetAnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = -1;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \Datetime('2008-08-08');
        $ref = new YetAnotherType();
        $ref->yourInt = 30;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = -1;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \Datetime('2010-10-10');
        $ref = new YetAnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
    }
    
    public function testAdvancedUpdateByText()
    {
        $type = new AdvancedUpdateType();
        $type->myText = "Four";
        
        $any = new \Good\Manners\Condition\EqualTo($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myInt = 455;
        
        $this->storage1->modifyAny($any, $modifications);
        
        $expectedResults = array();
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 455;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \Datetime('2004-04-04');
        $ins->myReference = null;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \Datetime('2005-05-05');
        $ref = new YetAnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \Datetime('2008-08-08');
        $ref = new YetAnotherType();
        $ref->yourInt = 30;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \Datetime('2010-10-10');
        $ref = new YetAnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
    }
    
    public function testAdvancedUpdateEqualsNull()
    {
        $type = new AdvancedUpdateType();
        $type->myInt = null;
        
        $any = new \Good\Manners\Condition\EqualTo($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myFloat = 666.666;
        
        $this->storage1->modifyAny($any, $modifications);
        
        $type = new AdvancedUpdateType();
        $type->myFloat = null;
        
        $any = new \Good\Manners\Condition\EqualTo($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myInt = 666;
        
        $this->storage1->modifyAny($any, $modifications);
        
        $type = new AdvancedUpdateType();
        $type->myText = null;
        
        $any = new \Good\Manners\Condition\EqualTo($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myDatetime = new Datetime('2066-06-06');
        
        $this->storage1->modifyAny($any, $modifications);
        
        $type = new AdvancedUpdateType();
        $type->myDatetime = null;
        
        $any = new \Good\Manners\Condition\EqualTo($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myText = "Six Six Six";
        
        $this->storage1->modifyAny($any, $modifications);
        
        $type = new AdvancedUpdateType();
        $type->myReference = null;
        
        $any = new \Good\Manners\Condition\EqualTo($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myInt = 777;
        
        $this->storage1->modifyAny($any, $modifications);
        
        $expectedResults = array();
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 777;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \Datetime('2004-04-04');
        $ins->myReference = null;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 666;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \Datetime('2005-05-05');
        $ref = new YetAnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \Datetime('2066-06-06');
        $ref = new YetAnotherType();
        $ref->yourInt = 30;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \Datetime('2010-10-10');
        $ref = new YetAnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 666.666;
        $ins->myText = "Six Six Six";
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
    }
    
    public function testAdvancedUpdateDoesNotEqualNullIntAndText()
    {
        $type = new AdvancedUpdateType();
        $type->myInt = null;
        
        $any = new \Good\Manners\Condition\NotEqualTo($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myFloat = 666.666;
        
        $this->storage1->modifyAny($any, $modifications);
        
        $type = new AdvancedUpdateType();
        $type->myText = null;
        
        $any = new \Good\Manners\Condition\NotEqualTo($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myDatetime = new Datetime('2066-06-06');
        
        $this->storage1->modifyAny($any, $modifications);
        
        $expectedResults = array();
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 4;
        $ins->myFloat = 666.666;
        $ins->myText = "Four";
        $ins->myDatetime = new \Datetime('2066-06-06');
        $ins->myReference = null;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 5;
        $ins->myFloat = 666.666;
        $ins->myText = "Five";
        $ins->myDatetime = new \Datetime('2066-06-06');
        $ref = new YetAnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 8;
        $ins->myFloat = 666.666;
        $ins->myText = null;
        $ins->myDatetime = new \Datetime('2008-08-08');
        $ref = new YetAnotherType();
        $ref->yourInt = 30;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 10;
        $ins->myFloat = 666.666;
        $ins->myText = "Ten";
        $ins->myDatetime = new \Datetime('2066-06-06');
        $ref = new YetAnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = new \Datetime('2066-06-06');
        $ref = new YetAnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
    }
    
    public function testAdvancedUpdateDoesNotEqualNullFloatAndDate()
    {
        $type = new AdvancedUpdateType();
        $type->myFloat = null;
        
        $any = new \Good\Manners\Condition\NotEqualTo($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myInt = 666;
        
        $this->storage1->modifyAny($any, $modifications);
        
        $type = new AdvancedUpdateType();
        $type->myDatetime = null;
        
        $any = new \Good\Manners\Condition\NotEqualTo($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myText = "Six Six Six";
        
        $this->storage1->modifyAny($any, $modifications);
        
        $expectedResults = array();
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 666;
        $ins->myFloat = 4.4;
        $ins->myText = "Six Six Six";
        $ins->myDatetime = new \Datetime('2004-04-04');
        $ins->myReference = null;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Six Six Six";
        $ins->myDatetime = new \Datetime('2005-05-05');
        $ref = new YetAnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 666;
        $ins->myFloat = 10.10;
        $ins->myText = "Six Six Six";
        $ins->myDatetime = new \Datetime('2008-08-08');
        $ref = new YetAnotherType();
        $ref->yourInt = 30;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 666;
        $ins->myFloat = 10.10;
        $ins->myText = "Six Six Six";
        $ins->myDatetime = new \Datetime('2010-10-10');
        $ref = new YetAnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 666;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
    }
    
    public function testAdvancedUpdateDoesNotEqualNullReference()
    {
        $type = new AdvancedUpdateType();
        $type->myReference = null;
        
        $any = new \Good\Manners\Condition\NotEqualTo($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myInt = 777;
        
        $this->storage1->modifyAny($any, $modifications);
        
        $expectedResults = array();
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \Datetime('2004-04-04');
        $ins->myReference = null;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 777;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \Datetime('2005-05-05');
        $ref = new YetAnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 777;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \Datetime('2008-08-08');
        $ref = new YetAnotherType();
        $ref->yourInt = 30;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 777;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \Datetime('2010-10-10');
        $ref = new YetAnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 777;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
    }
    
    public function testAdvancedUpdateByExistingReference()
    {
        // Let's firt mess up the data a little, so we can test
        // it changes exactly as much as it should
        $type = new YetAnotherType();
        $any = new \Good\Manners\Condition\EqualTo($type);
        $modifications = new YetAnotherType();
        $modifications->yourInt = 42;
        $this->storage1->modifyAny($any, $modifications);
        // one more change:
        $type = new AdvancedUpdateType();
        $type->myInt = 8;
        $any = new \Good\Manners\Condition\EqualTo($type);
        $resolver = new AdvancedUpdateTypeResolver();
        $resolver->resolveMyReference();
        $collection = $this->storage1->getCollection($any, $resolver);
        $ref = $collection->getNext()->myReference;
        $type = new AdvancedUpdateType();
        $type->myInt = 10;
        $any = new \Good\Manners\Condition\EqualTo($type);
        $modifications = new AdvancedUpdateType();
        $modifications->myReference = $ref;
        $this->storage1->modifyAny($any, $modifications);
        
        // Now we get our reference
        // (it's already in $ref, but it would be mixing of concerns if we relied on that)
        $type = new AdvancedUpdateType();
        $type->myInt = 10;
        $any = new \Good\Manners\Condition\EqualTo($type);
        $resolver = new AdvancedUpdateTypeResolver();
        $resolver->resolveMyReference();
        $collection = $this->storage1->getCollection($any, $resolver);
        $ref = $collection->getNext()->myReference;
        
        // And now we can finally do the real test
        $type = new AdvancedUpdateType();
        $type->myReference = $ref;
        
        $any = new \Good\Manners\Condition\EqualTo($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myInt = 1;
        
        $this->storage1->modifyAny($any, $modifications);
        
        $expectedResults = array();
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \Datetime('2004-04-04');
        $ins->myReference = null;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \Datetime('2005-05-05');
        $ref = new YetAnotherType();
        $ref->yourInt = 42;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 1;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \Datetime('2008-08-08');
        $ref = new YetAnotherType();
        $ref->yourInt = 42;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 1;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \Datetime('2010-10-10');
        $ref = new YetAnotherType();
        $ref->yourInt = 42;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 42;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
    }
    
    public function testAdvancedUpdateObjectAndReference()
    {
        $type = new AdvancedUpdateType();
        $type->myInt = 5;
        
        $cond1 = new \Good\Manners\Condition\GreaterThan($type);
        
        $type = new AdvancedUpdateType();
        $type->myReference = new YetAnotherType();
        $type->myReference->yourInt = 10;
        
        $cond2 = new \Good\Manners\Condition\GreaterThan($type);
        
        $any = new \Good\Manners\Condition\AndCondition($cond1, $cond2);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myInt = 100;
        $modifications->myReference = new YetAnotherType();
        $modifications->myReference->yourInt = 100;
        
        $this->storage1->modifyAny($any, $modifications);
        
        $expectedResults = array();
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \Datetime('2004-04-04');
        $ins->myReference = null;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \Datetime('2005-05-05');
        $ref = new YetAnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 100;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \Datetime('2008-08-08');
        $ref = new YetAnotherType();
        $ref->yourInt = 100;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 100;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \Datetime('2010-10-10');
        $ref = new YetAnotherType();
        $ref->yourInt = 100;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
    }
    
    public function testAdvancedUpdateObjectDeepReference()
    {
        // First insert a couple ThirdTypes into our dataset
        $type = new AdvancedUpdateType();
        $any = new \Good\Manners\Condition\EqualTo($type);
        $resolver = new AdvancedUpdateTypeResolver();
        $resolver->resolveRef();
        $collection = $this->storage1->getCollection($any, $resolver);
        
        foreach ($collection as $type)
        {
            if ($type->myInt == 4)
            {
                $ref = new ThirdType();
                $refref = new YetAnotherType();
                $refref->yourInt = 500;
                $ref->ref = $refref;
                $this->storage1->insert($ref);
                $type->ref = $ref;
            }
            else if ($type->myInt == 5)
            {
                $ref = new ThirdType();
                $refref = new YetAnotherType();
                $refref->yourInt = 300;
                $ref->ref = $refref;
                $this->storage1->insert($ref);
                $type->ref = $ref;
            }
            else if ($type->myInt == 8)
            {
                $ref = new ThirdType();
                $refref = new YetAnotherType();
                $refref->yourInt = 400;
                $ref->ref = $refref;
                $this->storage1->insert($ref);
                $type->ref = $ref;
            }
            else if ($type->myInt == 10)
            {
                $ref = new ThirdType();
                $refref = new YetAnotherType();
                $refref->yourInt = 200;
                $ref->ref = $refref;
                $this->storage1->insert($ref);
                $type->ref = $ref;
            }
        }
        
        $type = new AdvancedUpdateType();
        $type->ref = new ThirdType();
        $type->ref->ref = new YetAnotherType();
        $type->ref->ref->yourInt = 300;
        
        $any = new \Good\Manners\Condition\GreaterThan($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myInt = 99999;
        
        $this->storage1->modifyAny($any, $modifications);
        
        $expectedResults = array();
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 99999;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \Datetime('2004-04-04');
        $ins->myReference = null;
        $ins->ref = new ThirdType();
        $ins->ref->ref = new YetAnotherType();
        $ins->ref->ref->yourInt = 500;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \Datetime('2005-05-05');
        $ref = new YetAnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $ins->ref = new ThirdType();
        $ins->ref->ref = new YetAnotherType();
        $ins->ref->ref->yourInt = 300;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 99999;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \Datetime('2008-08-08');
        $ref = new YetAnotherType();
        $ref->yourInt = 30;
        $ins->myReference = $ref;
        $ins->ref = new ThirdType();
        $ins->ref->ref = new YetAnotherType();
        $ins->ref->ref->yourInt = 400;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \Datetime('2010-10-10');
        $ref = new YetAnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $ins->ref = new ThirdType();
        $ins->ref->ref = new YetAnotherType();
        $ins->ref->ref->yourInt = 200;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
    }
    
    public function testAdvancedUpdateObjectDeepReferenceInverse()
    {
        // First insert a couple ThirdTypes into our dataset
        $type = new AdvancedUpdateType();
        $any = new \Good\Manners\Condition\EqualTo($type);
        $resolver = new AdvancedUpdateTypeResolver();
        $resolver->resolveRef();
        $collection = $this->storage1->getCollection($any, $resolver);
        
        foreach ($collection as $type)
        {
            if ($type->myInt == 4)
            {
                $ref = new ThirdType();
                $refref = new YetAnotherType();
                $refref->yourInt = 500;
                $ref->ref = $refref;
                $this->storage1->insert($ref);
                $type->ref = $ref;
            }
            else if ($type->myInt == 5)
            {
                $ref = new ThirdType();
                $refref = new YetAnotherType();
                $refref->yourInt = 300;
                $ref->ref = $refref;
                $this->storage1->insert($ref);
                $type->ref = $ref;
            }
            else if ($type->myInt == 8)
            {
                $ref = new ThirdType();
                $refref = new YetAnotherType();
                $refref->yourInt = 400;
                $ref->ref = $refref;
                $this->storage1->insert($ref);
                $type->ref = $ref;
            }
            else if ($type->myInt == 10)
            {
                $ref = new ThirdType();
                $refref = new YetAnotherType();
                $refref->yourInt = 200;
                $ref->ref = $refref;
                $this->storage1->insert($ref);
                $type->ref = $ref;
            }
        }
        
        $type = new AdvancedUpdateType();
        $type->myInt = 5;
        
        $any = new \Good\Manners\Condition\GreaterThan($type);
        
        $modifications = new AdvancedUpdateType();
        $modifications->ref = new ThirdType();
        $modifications->ref->ref = new YetAnotherType();
        $modifications->ref->ref->yourInt = 666;
        
        $this->storage1->modifyAny($any, $modifications);
        
        $expectedResults = array();
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \Datetime('2004-04-04');
        $ins->myReference = null;
        $ins->ref = new ThirdType();
        $ins->ref->ref = new YetAnotherType();
        $ins->ref->ref->yourInt = 500;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \Datetime('2005-05-05');
        $ref = new YetAnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $ins->ref = new ThirdType();
        $ins->ref->ref = new YetAnotherType();
        $ins->ref->ref->yourInt = 300;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \Datetime('2008-08-08');
        $ref = new YetAnotherType();
        $ref->yourInt = 30;
        $ins->myReference = $ref;
        $ins->ref = new ThirdType();
        $ins->ref->ref = new YetAnotherType();
        $ins->ref->ref->yourInt = 666;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \Datetime('2010-10-10');
        $ref = new YetAnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $ins->ref = new ThirdType();
        $ins->ref->ref = new YetAnotherType();
        $ins->ref->ref->yourInt = 666;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
    }
    
    public function testCircularUpdateAndConditionBug()
    {
        // issue #38
        $type = new AdvancedUpdateType();
        $type->myInt = 5;
        
        $cond1 = new \Good\Manners\Condition\GreaterThan($type);
        
        $type = new AdvancedUpdateType();
        $type->myReference = new YetAnotherType();
        $type->myReference->yourInt = 10;
        
        $cond2 = new \Good\Manners\Condition\GreaterThan($type);
        
        $any = new \Good\Manners\Condition\AndCondition($cond1, $cond2);
        
        $modifications = new AdvancedUpdateType();
        $modifications->myInt = 1;
        $modifications->myReference = new YetAnotherType();
        $modifications->myReference->yourInt = 1;
        
        $this->storage1->modifyAny($any, $modifications);
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \Datetime('2004-04-04');
        $ins->myReference = null;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \Datetime('2005-05-05');
        $ref = new YetAnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 1;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \Datetime('2008-08-08');
        $ref = new YetAnotherType();
        $ref->yourInt = 1;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = 1;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \Datetime('2010-10-10');
        $ref = new YetAnotherType();
        $ref->yourInt = 1;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $ins = new AdvancedUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new YetAnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $ins->ref = null;
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
    }
}

?>