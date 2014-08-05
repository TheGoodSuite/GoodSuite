<?php

// helper classes:
class ChildFactoryT1 extends \Good\Manners\DefaultStorableFactory
{
    public function createStorable($type)
    {
        if ($type == 'ParentType1')
        {
            return new ChildType1();
        }
        else
        {
            return parent::createStorable($type);
        }
    }
}
class ChildFactoryT2 extends \Good\Manners\DefaultStorableFactory
{
    public function createStorable($type)
    {
        if ($type == 'ParentType2')
        {
            return new ChildType2();
        }
        else
        {
            return parent::createStorable($type);
        }
    }
}
class ChildFactoryBoth extends \Good\Manners\DefaultStorableFactory
{
    public function createStorable($type)
    {
        if ($type == 'ParentType1')
        {
            return new ChildType1();
        }
        else if ($type == 'ParentType2')
        {
            return new ChildType2();
        }
        else
        {
            return parent::createStorable($type);
        }
    }
}
class IndependentFactoryT1 implements \Good\Manners\StorableFactory
{
    public function createStorable($type)
    {
        if ($type == 'ParentType1')
        {
            return new ChildType1();
        }
        else if ($type == 'ParentType2')
        {
            return new ParentType2();
        }
    }
}
class IndependentFactoryT2 implements \Good\Manners\StorableFactory
{
    public function createStorable($type)
    {
        if ($type == 'ParentType1')
        {
            return new ParentType1();
        }
        else if ($type == 'ParentType2')
        {
            return new ChildType2();
        }
    }
}
class IndependentFactoryBoth implements \Good\Manners\StorableFactory
{
    public function createStorable($type)
    {
        if ($type == 'ParentType1')
        {
            return new ChildType1();
        }
        else if ($type == 'ParentType2')
        {
            return new ChildType2();
        }
        else
        {
            return parent::createStorable($type);
        }
    }
}

/** 
 * @runTestsInSeparateProcesses
 */
abstract class GoodMannersChildTypesTest extends PHPUnit_Framework_TestCase
{
    private $storage;
    
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
        file_put_contents(dirname(__FILE__) . '/../testInputFiles/ParentType1.datatype', 
                                                                            "datatype ParentType1\n" .
                                                                            "{\n" .
                                                                            "   int myInt;\n" .
                                                                            "   float myFloat;\n".
                                                                            "   text myText;\n" .
                                                                            "   datetime myDatetime;\n" .
                                                                            '   "ParentType2" myOtherType;' . "\n" .
                                                                            '   "ParentType1" myCircular;' . "\n" .
                                                                            "}\n");
        
        file_put_contents(dirname(__FILE__) . '/../testInputFiles/ParentType2.datatype', 
                                                                            "datatype ParentType2 { int yourInt; }");
    
        $rolemodel = new \Good\Rolemodel\Rolemodel();
        $schema = $rolemodel->createSchema(array(dirname(__FILE__) . '/../testInputFiles/ParentType1.datatype',
                                                 dirname(__FILE__) . '/../testInputFiles/ParentType2.datatype'));

        $service = new \Good\Service\Service();
        $service->compile(array(new \Good\Manners\Modifier\Storable()), $schema, dirname(__FILE__) . '/../generated/');
        
        require dirname(__FILE__) . '/../generated/ParentType1.datatype.php';
        require dirname(__FILE__) . '/../generated/ParentType2.datatype.php';
        
        require dirname(__FILE__) . '/../generated/ParentType1Resolver.php';
        require dirname(__FILE__) . '/../generated/ParentType2Resolver.php';
        
        require dirname(__FILE__) . '/GoodMannersChildTypes.php';
    }
    
    public static function _tearDownAfterClass()
    {
        unlink(dirname(__FILE__) . '/../testInputFiles/ParentType1.datatype');
        unlink(dirname(__FILE__) . '/../testInputFiles/ParentType2.datatype');
        unlink(dirname(__FILE__) . '/../generated/ParentType1.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/ParentType2.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/ParentType1Resolver.php');
        unlink(dirname(__FILE__) . '/../generated/ParentType2Resolver.php');
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
        $db->query('TRUNCATE parenttype1');
        $db->query('TRUNCATE parenttype1');
        
        $storage = $this->getNewStorage();
        
        $ins = new ParentType1();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \Datetime('2004-04-04');
        $ref = new ParentType2();
        $ref->yourInt = 90;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $storage->insert($ins);
        $first = $ins;
        
        $ins = new ParentType1();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \Datetime('2005-05-05');
        $ref = new ParentType2();
        $ref->yourInt = 80;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $storage->insert($ins);
        
        $ins = new ParentType1();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \Datetime('2008-08-08');
        $ref = new ParentType2();
        $ref = new ParentType2();
        $ref->yourInt = 40;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $storage->insert($ins);
        
        $ins = new ParentType1();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \Datetime('2010-10-10');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $storage->insert($ins);
        
        $ins = new ParentType1();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new ParentType2();
        $ref->yourInt = 5;
        $ins->myOtherType = $ref;
        $ins->myCircular = $first;
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
        $db->query('TRUNCATE parenttype1');
        $db->query('TRUNCATE parenttype2');
        
        $this->_tearDownAfterClass();
    }
    
    public function checkResults($type1, $type2)
    {
        // At the moment we don't have a proper api to get any,
        // but this trick does do the same
        $type = new ParentType1();
        $any = new \Good\Manners\Condition\GreaterThan($type);
        
        $resolver = new ParentType1Resolver();
        $resolver->resolveMyOtherType();
        $collection = $this->storage->getCollection($any, $resolver);
        
        foreach ($collection as $obj)
        {
            $this->assertInstanceOf($type1, $obj);
            
            if ($obj->myOtherType != null)
            {
                $this->assertInstanceOf($type2, $obj->myOtherType);
            }
            
            if ($obj->myCircular != null)
            {
                $this->assertInstanceOf($type1, $obj->myCircular);
            }
        }
    }
    
    public function testRegisterType1()
    {
        $this->storage->registerType('ParentType1', 'ChildType1');
        
        $this->checkResults('ChildType1', 'ParentType2');
    }
    
    public function testRegisterType2()
    {
        $this->storage->registerType('ParentType2', 'ChildType2');
        
        $this->checkResults('ParentType1', 'ChildType2');
    }
    
    public function testRegisterBothTypes()
    {
        $this->storage->registerType('ParentType1', 'ChildType1');
        $this->storage->registerType('ParentType2', 'ChildType2');
        
        $this->checkResults('ChildType1', 'ChildType2');
    }
    
    public function testRegisterToDefaultFactoryType1()
    {
        $factory = new \Good\Manners\DefaultStorableFactory();
        $factory->registerType('ParentType1', 'ChildType1');
        
        $this->storage->setStorableFactory($factory);
        
        $this->checkResults('ChildType1', 'ParentType2');
    }
    
    public function testRegisterToDefaultFactoryType2()
    {
        $factory = new \Good\Manners\DefaultStorableFactory();
        $factory->registerType('ParentType2', 'ChildType2');
        
        $this->storage->setStorableFactory($factory);
        
        $this->checkResults('ParentType1', 'ChildType2');
    }
    
    public function testRegisterToDefaultFactoryBothTypes()
    {
        $factory = new \Good\Manners\DefaultStorableFactory();
        $factory->registerType('ParentType1', 'ChildType1');
        $factory->registerType('ParentType2', 'ChildType2');
        
        $this->storage->setStorableFactory($factory);
        
        $this->checkResults('ChildType1', 'ChildType2');
    }
    
    public function testChildFactoryType1()
    {
        $factory = new ChildFactoryT1();
        
        $this->storage->setStorableFactory($factory);
        
        $this->checkResults('ChildType1', 'ParentType2');
    }
    
    public function testChildFactoryType2()
    {
        $factory = new ChildFactoryT2();
        
        $this->storage->setStorableFactory($factory);
        
        $this->checkResults('ParentType1', 'ChildType2');
    }
    
    public function testChildFactoryBothTypes()
    {
        $factory = new ChildFactoryBoth();
        
        $this->storage->setStorableFactory($factory);
        
        $this->checkResults('ChildType1', 'ChildType2');
    }
    
    public function testIndependentFactoryType1()
    {
        $factory = new IndependentFactoryT1();
        
        $this->storage->setStorableFactory($factory);
        
        $this->checkResults('ChildType1', 'ParentType2');
    }
    
    public function testIndependentFactoryType2()
    {
        $factory = new IndependentFactoryT2();
        
        $this->storage->setStorableFactory($factory);
        
        $this->checkResults('ParentType1', 'ChildType2');
    }
    
    public function testIndependentFactoryBothTypes()
    {
        $factory = new IndependentFactoryBoth();
        
        $this->storage->setStorableFactory($factory);
        
        $this->checkResults('ChildType1', 'ChildType2');
    }
}

?>