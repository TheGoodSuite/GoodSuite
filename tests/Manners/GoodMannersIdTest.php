<?php

/** 
 * @runTestsInSeparateProcesses
 */
abstract class GoodMannersIdTest extends PHPUnit_Framework_TestCase
{
    private $storage;
    
    abstract public function getNewStorage();
    // this function should be removed, but is used for clearing the database at the moment
    abstract public function getNewDb();
    
    public static function _setUpBeforeClass()
    {
        // Garbage collector causes segmentation fault, so we disable 
        // for the duration of the test case
        gc_disable();
        file_put_contents(dirname(__FILE__) . '/../testInputFiles/IdType.datatype', 
                                                                            "datatype IdType\n" .
                                                                            "{" .
                                                                            "   text myText;\n" .
                                                                            '   "IdType" reference;' . "\n" .
                                                                            "}\n");
    
        $rolemodel = new \Good\Rolemodel\Rolemodel();
        $schema = $rolemodel->createSchema(array('IdType' => dirname(__FILE__) . '/../testInputFiles/IdType.datatype'));

        $service = new \Good\Service\Service();
        $service->compile(array(new \Good\Manners\Modifier\Storable()), $schema, dirname(__FILE__) . '/../generated/');
        
        require dirname(__FILE__) . '/../generated/IdType.datatype.php';
        
        require dirname(__FILE__) . '/../generated/IdTypeResolver.php';
    }
    
    public static function _tearDownAfterClass()
    {
        unlink(dirname(__FILE__) . '/../testInputFiles/IdType.datatype');
        unlink(dirname(__FILE__) . '/../generated/IdType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/IdTypeResolver.php');
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
        $db->query('TRUNCATE idtype');
        
        $storage = $this->getNewStorage();
        
        $ins = new IdType();
        $ins->myText = 'a';
        $ins->reference = null;
        $storage->insert($ins);
        $reference = $ins;
        
        $ins = new IdType();
        $ins->myText = 'c';
        $ins->reference = $reference;
        $storage->insert($ins);
        
        $ins = new IdType();
        $ins->myText = 'b';
        $ins->reference = $reference;
        $storage->insert($ins);
        
        $storage->flush();
        
        // new Storage, so communication will have to go through data storage
        $this->storage = $this->getNewStorage();
    }
    
    public function tearDown()
    {
        // Just doing this already to make sure the deconstructor will hasve
        // side-effects at an unspecified moment...
        // (at which point the database will probably be in a wrong state for this)
        $this->storage->flush();
        
        // this should be handled through the GoodManners API once that is implemented
        $db = $this->getNewDb();
        $db->query('TRUNCATE idtype');
        
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
            if ($hay->myText == $needle->myText &&
                (($hay->reference === null && $needle->reference === null) ||
                 $hay->reference !== null && $needle->reference !== null &&
                 $hay->reference->myText == $needle->reference->myText))
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
    
    public function testGetById()
    {
        // first we get a result from the database to find out what irs id is
        
        // Get the object with text == 'b'
        $type = new IdType();
        $type->myText = 'b';
        $any = new \Good\Manners\Condition\EqualTo($type);
        
        $collection = $this->storage->getCollection($any, IdType::resolver());
        
        $idHolder = $collection->getNext();
        
        // There's still some improvement to be made here ($id->get())
        $id = IdType::id($idHolder->getId());
        $any = new \Good\Manners\Condition\EqualTo($id);
        
        $resolver = IdType::resolver();
        $resolver->resolveReference();
        $collection = $this->storage->getCollection($any, $resolver);
        
        $expectedResults = array();
        
        $expected = new IdType();
        $expected->myText = 'b';
        $expected->reference = new IdType();
        $expected->reference->myText = 'a';
        
        $expectedResults[] = $expected;
        
        foreach ($collection as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
            
            array_splice($expectedResults, $pos, 1);
        }
        
        $this->assertSame(array(), $expectedResults);        
    }
    
    public function testDeleteById()
    {
        // first we get a result from the database to find out what irs id is
        
        // Get the object with text == 'b'
        $type = new IdType();
        $type->myText = 'b';
        $any = new \Good\Manners\Condition\EqualTo($type);
        
        $collection = $this->storage->getCollection($any, IdType::resolver());
        
        $idHolder = $collection->getNext();
        
        $id = IdType::id($idHolder->getId());
        $id->setStorage($this->storage);
        $id->delete();
        
        $this->storage->flush();
        
        // get all trick
        $any = new \Good\Manners\Condition\EqualTo(new IdType());
        
        $resolver = IdType::resolver();
        $resolver->resolveReference();
        $collection = $this->storage->getCollection($any, $resolver);
        
        $expectedResults = array();
        
        $expected = new IdType();
        $expected->myText = 'a';
        $expected->reference = null;
        $expectedResults[] = $expected;
        
        $expected = new IdType();
        $expected->myText = 'c';
        $expected->reference = new IdType();
        $expected->reference->myText = 'a';
        $expectedResults[] = $expected;
        
        foreach ($collection as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
            
            array_splice($expectedResults, $pos, 1);
        }
        
        $this->assertSame(array(), $expectedResults);        
    }
    
    public function testIdInCondition()
    {
        // first we get a result from the database to find out what irs id is
        
        // Get the object with text == 'a'
        $type = new IdType();
        $type->myText = 'a';
        $any = new \Good\Manners\Condition\EqualTo($type);
        
        $collection = $this->storage->getCollection($any, IdType::resolver());
        
        $idHolder = $collection->getNext();
        
        $id = IdType::id($idHolder->getId());
        $referencing = new IdType();
        $referencing->reference = $id;
        
        $any = new \Good\Manners\Condition\EqualTo($referencing);
        
        $resolver = IdType::resolver();
        $resolver->resolveReference();
        $collection = $this->storage->getCollection($any, $resolver);
        
        $expectedResults = array();
        
        $expected = new IdType();
        $expected->myText = 'b';
        $expected->reference = new IdType();
        $expected->reference->myText = 'a';
        $expectedResults[] = $expected;
        
        $expected = new IdType();
        $expected->myText = 'c';
        $expected->reference = new IdType();
        $expected->reference->myText = 'a';
        $expectedResults[] = $expected;
        
        foreach ($collection as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
            
            array_splice($expectedResults, $pos, 1);
        }
        
        $this->assertSame(array(), $expectedResults);        
    }
    
    public function testIdInModification()
    {
        $type = new IdType();
        $type->myText = 'a';
        $any = new \Good\Manners\Condition\EqualTo($type);
        
        $collection = $this->storage->getCollection($any, IdType::resolver());
        
        $a = $collection->getNext();
        
        $type = new IdType();
        $type->myText = 'b';
        $any = new \Good\Manners\Condition\EqualTo($type);
        
        $collection = $this->storage->getCollection($any, IdType::resolver());
        
        $b = $collection->getNext();
        
        $id = IdType::id($b->getId());
        
        $a->reference = $id;
        
        $this->storage->flush();
        
        // get all trick
        $any = new \Good\Manners\Condition\EqualTo(new IdType());
        
        $resolver = IdType::resolver();
        $resolver->resolveReference();
        $collection = $this->storage->getCollection($any, $resolver);
        
        $expectedResults = array();
        
        $expected = new IdType();
        $expected->myText = 'a';
        $expected->reference = new IdType();
        $expected->reference->myText = 'b';
        $expectedResults[] = $expected;
        
        $expected = new IdType();
        $expected->myText = 'b';
        $expected->reference = new IdType();
        $expected->reference->myText = 'a';
        $expectedResults[] = $expected;
        
        $expected = new IdType();
        $expected->myText = 'c';
        $expected->reference = new IdType();
        $expected->reference->myText = 'a';
        $expectedResults[] = $expected;
        
        foreach ($collection as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
            
            array_splice($expectedResults, $pos, 1);
        }
        
        $this->assertSame(array(), $expectedResults);    
    }
    
    public function testIdInModifyAny()
    {
        $type = new IdType();
        $type->myText = 'b';
        $any = new \Good\Manners\Condition\EqualTo($type);
        
        $collection = $this->storage->getCollection($any, IdType::resolver());
        
        $idHolder = $collection->getNext();
        
        $id = IdType::id($idHolder->getId());
        
        $a = new IdType();
        $a->myText = 'a';
        $b = new IdType();
        $b->myText = 'b';
        
        $aOrB = new \Good\Manners\Condition\OrCondition(
            new \Good\Manners\Condition\EqualTo($a),
            new \Good\Manners\Condition\EqualTo($b));
        
        $referenceIsId = new IdType();
        $referenceIsId->reference = $id;
        
        $this->storage->modifyAny($aOrB, $referenceIsId);
        
        // get all trick
        $any = new \Good\Manners\Condition\EqualTo(new IdType());
        
        $resolver = IdType::resolver();
        $resolver->resolveReference();
        $collection = $this->storage->getCollection($any, $resolver);
        
        $expectedResults = array();
        
        $expected = new IdType();
        $expected->myText = 'a';
        $expected->reference = new IdType();
        $expected->reference->myText = 'b';
        $expectedResults[] = $expected;
        
        $expected = new IdType();
        $expected->myText = 'b';
        $expected->reference = new IdType();
        $expected->reference->myText = 'b';
        $expectedResults[] = $expected;
        
        $expected = new IdType();
        $expected->myText = 'c';
        $expected->reference = new IdType();
        $expected->reference->myText = 'a';
        $expectedResults[] = $expected;
        
        foreach ($collection as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);
            
            array_splice($expectedResults, $pos, 1);
        }
        
        $this->assertSame(array(), $expectedResults);    
    }
}

?>