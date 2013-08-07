<?php

/** 
 * @runTestsInSeparateProcesses
 */
abstract class GoodMannersPersistenceTest extends PHPUnit_Framework_TestCase
{    
    abstract public function getNewStore();
    // this function should be removed, but is used for clearing the database at the moment
    abstract public function getNewDb();
    
    public function _setUpEachHalf()
    {
        // Garbage collector causes segmentation fault, so we disable 
        // for the duration of the test case
        gc_disable();
        file_put_contents(dirname(__FILE__) . '/../testInputFiles/PersistenceType.datatype', 
                                                                            "int myInt\n" .
                                                                            "float myFloat\n".
                                                                            "text myText\n" .
                                                                            "datetime myDatetime\n");
    
        $rolemodel = new \Good\Rolemodel\Rolemodel();
        $schema = $rolemodel->createSchema(array('PersistenceType' => 
                            dirname(__FILE__) . '/../testInputFiles/PersistenceType.datatype'));

        $service = new \Good\Service\Service();
        $service->compile(array(new \Good\Manners\Modifier\Storable()), $schema, dirname(__FILE__) . '/../generated/');
        
        require dirname(__FILE__) . '/../generated/BasePersistenceType.datatype.php';
        
        $service->requireClasses(array('PersistenceType'));
        
        require dirname(__FILE__) . '/../generated/PersistenceTypeResolver.php';
    }
    
    
    public function _setUp()
    {
        // just doubling this up (from tearDown) to be sure
        // this should be handled natively once that is implemented
        $db = $this->getNewDb();
        $db->query('TRUNCATE persistencetype');
    }
    
    public function _tearDown()
    {
        // this should be handled through the GoodManners API once that is implemented
        $db = $this->getNewDb();
        $db->query('TRUNCATE persistencetype');
        
        unlink(dirname(__FILE__) . '/../testInputFiles/PersistenceType.datatype');
        unlink(dirname(__FILE__) . '/../generated/BasePersistenceType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/PersistenceType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/PersistenceTypeResolver.php');
        unlink(dirname(__FILE__) . '/../generated/GeneratedBaseClass.php');
        
        if (ini_get('zend.enable_gc'))
        {
            gc_enable();
        }
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
            // this will always fail
            // basically, we tested before with a couple less restirctions
            // and now we just use the general function to get nice ouput
            // it'll contain some differences that don't matter, but that's 
            // a small price to pay
            $this->assertContains($needle, $haystack);
        }
        
        return $pos;
    }
    
    public function testInsert()
    {
        $this->_setUpEachHalf();
        $this->_setUp();
        
        $store = $this->getNewStore();
    
        $ins = new PersistenceType();
        $ins->setMyInt(4);
        $ins->setMyFloat(4.4);
        $ins->setMyText("Four");
        $ins->setMyDatetime(new \Datetime('2004-04-04'));
        $store->insert($ins);
        
        $ins = new PersistenceType();
        $ins->setMyInt(5);
        $ins->setMyFloat(8.8);
        $ins->setMyText("Ten");
        $ins->setMyDatetime(new \Datetime('2012-12-12'));
        $store->insert($ins);
        
        $expectedResults = array();
        
        // Actually those two tests are just one test
        // (continued below)
    }
    
    /**
     * @depends testInsert
     */
    public function testGet()
    {
        // However, I wanted the two parts to run in different prcoesses
        // and this was an easy way to accomplish that.
        $this->_setUpEachHalf();
        
        $store = $this->getNewStore();
        
        $expectedResults = array();
        
        $ins = new PersistenceType();
        $ins->setMyInt(4);
        $ins->setMyFloat(4.4);
        $ins->setMyText("Four");
        $ins->setMyDatetime(new \Datetime('2004-04-04'));
        $expectedResults[] = $ins;
        
        $ins = new PersistenceType();
        $ins->setMyInt(5);
        $ins->setMyFloat(8.8);
        $ins->setMyText("Ten");
        $ins->setMyDatetime(new \Datetime('2012-12-12'));
        $expectedResults[] = $ins;
        
        // At the moment we don't have a proper api to get any,
        // but this trick does do the same
        $type = new PersistenceType();
        $any = new \Good\Manners\Condition\Greater($type);
        
        $resolver = new PersistenceTypeResolver();
        $collection = $store->getCollection($any, $resolver);
        
        while ($type = $collection->getNext())
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
            
            array_splice($expectedResults, $pos, 1);
        }
        
        $this->assertSame(array(), $expectedResults);
        
        $this->_tearDown();
    }
}

?>