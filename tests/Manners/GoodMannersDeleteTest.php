<?php

/** 
 * @runTestsInSeparateProcesses
 */
abstract class GoodMannersDeleteTest extends PHPUnit_Framework_TestCase
{
    private $storage1;
    private $storage2;
    
    abstract public function getNewStorage();
    // this function should be removed, but is used for clearing the database at the moment
    abstract public function getNewDb();
    
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
        file_put_contents(dirname(__FILE__) . '/../testInputFiles/DeleteType.datatype', 
                                                                            "int myInt\n" .
                                                                            "float myFloat\n".
                                                                            "text myText\n" .
                                                                            "datetime myDatetime\n" );
    
        $rolemodel = new \Good\Rolemodel\Rolemodel();
        $schema = $rolemodel->createSchema(array('DeleteType' => 
                                                        dirname(__FILE__) . '/../testInputFiles/DeleteType.datatype'));

        $service = new \Good\Service\Service();
        $service->compile(array(new \Good\Manners\Modifier\Storable()), $schema, dirname(__FILE__) . '/../generated/');
        
        require dirname(__FILE__) . '/../generated/BaseDeleteType.datatype.php';
        
        $service->requireClasses(array('DeleteType'));
        
        require dirname(__FILE__) . '/../generated/DeleteTypeResolver.php';
    }
    
    public static function _tearDownAfterClass()
    {
        unlink(dirname(__FILE__) . '/../testInputFiles/DeleteType.datatype');
        unlink(dirname(__FILE__) . '/../generated/BaseDeleteType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/DeleteType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/DeleteTypeResolver.php');
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
        $db->query('TRUNCATE deletetype');
        
        $storage = $this->getNewStorage();
        
        $ins = new DeleteType();
        $ins->setMyInt(4);
        $ins->setMyFloat(4.4);
        $ins->setMyText("Four");
        $ins->setMyDatetime(new \Datetime('2004-04-04'));
        $storage->insert($ins);
        
        $ins = new DeleteType();
        $ins->setMyInt(5);
        $ins->setMyFloat(null);
        $ins->setMyText("Five");
        $ins->setMyDatetime(new \Datetime('2005-05-05'));
        $storage->insert($ins);
        
        $ins = new DeleteType();
        $ins->setMyInt(8);
        $ins->setMyFloat(10.10);
        $ins->setMyText(null);
        $ins->setMyDatetime(new \Datetime('2008-08-08'));
        $storage->insert($ins);
        
        $ins = new DeleteType();
        $ins->setMyInt(10);
        $ins->setMyFloat(10.10);
        $ins->setMyText("Ten");
        $ins->setMyDatetime(new \Datetime('2010-10-10'));
        $storage->insert($ins);
        
        $ins = new DeleteType();
        $ins->setMyInt(null);
        $ins->setMyFloat(20.20);
        $ins->setMyText("Twenty");
        $ins->setMyDatetime(null);
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
        $db = $this->getNewDb();
        $db->query('TRUNCATE deletetype');
        
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
                $hay->getMyDatetime() == $needle->getMyDatetime())
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
            // We're always wrong here.
            //$this->assertContains($needle, $haystack);
            
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
        $type = new DeleteType();
        $any = new \Good\Manners\Condition\Greater($type);
        
        $resolver = new DeleteTypeResolver();
        
        $collection = $this->storage2->getCollection($any, $resolver);
        
        while ($type = $collection->getNext())
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expected);
            
            array_splice($expected, $pos, 1);
        }
        
        $this->assertSame(array(), $expected);        
    }
    
    public function testDelete()
    {
        // At the moment we don't have a proper api to get any,
        // but this trick does do the same
        $type = new DeleteType();
        $any = new \Good\Manners\Condition\Greater($type);
        
        $collection = $this->storage1->getCollection($any, new DeleteTypeResolver());
        
        while ($type = $collection->getNext())
        {
            if ($type->getMyInt() == 5 || $type->getMyInt() == 10)
            {
                $type->delete();
            }
        }
        
        $this->storage1->flush();
        
        $expectedResults = array();
        
        $ins = new DeleteType();
        $ins->setMyInt(4);
        $ins->setMyFloat(4.4);
        $ins->setMyText("Four");
        $ins->setMyDatetime(new \Datetime('2004-04-04'));
        $expectedResults[] = $ins;
        
        $ins = new DeleteType();
        $ins->setMyInt(8);
        $ins->setMyFloat(10.10);
        $ins->setMyText(null);
        $ins->setMyDatetime(new \Datetime('2008-08-08'));
        $expectedResults[] = $ins;
        
        $ins = new DeleteType();
        $ins->setMyInt(null);
        $ins->setMyFloat(20.20);
        $ins->setMyText("Twenty");
        $ins->setMyDatetime(null);
        $expectedResults[] = $ins;
        
        $this->checkResults($expectedResults);
    }
}

?>