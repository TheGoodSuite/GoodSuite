<?php

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
abstract class GoodMannersFetchTest extends \PHPUnit\Framework\TestCase
{
    private $storage;

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
        file_put_contents(dirname(__FILE__) . '/../testInputFiles/MyFetchType.datatype',
                                                                            "datatype MyFetchType\n" .
                                                                            "{" .
                                                                            "   int myInt;\n" .
                                                                            "   float myFloat;\n".
                                                                            "   text myText;\n" .
                                                                            "   datetime myDatetime;\n" .
                                                                            '   "OtherType" myOtherType;' . "\n" .
                                                                            '   "MyFetchType" myCircular;' . "\n" .
                                                                            "}\n");

        file_put_contents(dirname(__FILE__) . '/../testInputFiles/OtherType.datatype',
                                                                            "datatype OtherType { int yourInt; }");

        $rolemodel = new \Good\Rolemodel\Rolemodel();
        $schema = $rolemodel->createSchema(array(dirname(__FILE__) . '/../testInputFiles/MyFetchType.datatype',
                                                   dirname(__FILE__) . '/../testInputFiles/OtherType.datatype'));

        $service = new \Good\Service\Service();
        $service->compile(array(new \Good\Manners\Modifier\Storable()), $schema, dirname(__FILE__) . '/../generated/');

        require dirname(__FILE__) . '/../generated/MyFetchType.datatype.php';
        require dirname(__FILE__) . '/../generated/OtherType.datatype.php';

        require dirname(__FILE__) . '/../generated/MyFetchTypeResolver.php';
        require dirname(__FILE__) . '/../generated/OtherTypeResolver.php';

        require dirname(__FILE__) . '/../generated/MyFetchTypeCondition.php';
        require dirname(__FILE__) . '/../generated/OtherTypeCondition.php';
    }

    public static function _tearDownAfterClass()
    {
        unlink(dirname(__FILE__) . '/../testInputFiles/MyFetchType.datatype');
        unlink(dirname(__FILE__) . '/../testInputFiles/OtherType.datatype');
        unlink(dirname(__FILE__) . '/../generated/MyFetchType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/OtherType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/MyFetchTypeResolver.php');
        unlink(dirname(__FILE__) . '/../generated/OtherTypeResolver.php');
        unlink(dirname(__FILE__) . '/../generated/MyFetchTypeCondition.php');
        unlink(dirname(__FILE__) . '/../generated/OtherTypeCondition.php');
        unlink(dirname(__FILE__) . '/../generated/GeneratedBaseClass.php');

        if (ini_get('zend.enable_gc'))
        {
            gc_enable();
        }
    }

    public function setUp(): void
    {
        $this->_setUpBeforeClass();

        // just doubling this up (from tearDown) to be sure
        // this should be handled natively once that is implemented
        $this->truncateTable('myfetchtype');
        $this->truncateTable('othertype');

        $storage = $this->getNewStorage();

        $ins = new MyFetchType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $ref = new OtherType();
        $ref->yourInt = 90;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $storage->insert($ins);

        $ins = new MyFetchType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new OtherType();
        $ref->yourInt = 80;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $storage->insert($ins);

        $ins = new MyFetchType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ref = new OtherType();
        $ref->yourInt = 40;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $storage->insert($ins);

        $ins = new MyFetchType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $storage->insert($ins);

        $ins = new MyFetchType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new OtherType();
        $ref->yourInt = 5;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $storage->insert($ins);

        $storage->flush();

        // new Storage, so communication will have to go through data storage
        $this->storage = $this->getNewStorage();
    }

    public function tearDown(): void
    {
        // Just doing this already to make sure the deconstructor will hasve
        // side-effects at an unspecified moment...
        // (at which point the database will probably be in a wrong state for this)
        $this->storage->flush();

        // this should be handled through the GoodManners API once that is implemented
        $this->truncateTable('myfetchtype');
        $this->truncateTable('othertype');

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
                (($hay->myOtherType === null) ||
                // or neither is null (so we won't be calling functions on null)
                // and they are the same
                 ($hay->myOtherType !== null && $needle->myOtherType !== null &&
                  $hay->myOtherType->yourInt == $needle->myOtherType->yourInt)) &&
                // they are both null
                (($hay->myCircular === null) ||
                // or neither is null (so we won't be calling functions on null)
                // and they are the same
                 ($hay->myCircular !== null && $needle->myCircular !== null &&
                  $hay->myCircular->myInt == $needle->myCircular->myInt &&
                  $hay->myCircular->myFloat == $needle->myCircular->myFloat &&
                  $hay->myCircular->myText == $needle->myCircular->myText &&
                  $hay->myCircular->myDatetime == $needle->myCircular->myDatetime)))
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

    public function testFetchAll()
    {
        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $ref = new OtherType();
        $ref->yourInt = 90;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new OtherType();
        $ref->yourInt = 80;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ref = new OtherType();
        $ref->yourInt = 40;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new OtherType();
        $ref->yourInt = 5;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    public function testFetchLessThan()
    {
        $condition = MyFetchType::condition();
        $condition->myInt = new \Good\Manners\Condition\LessThan(5);

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $ref = new OtherType();
        $ref->yourInt = 90;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    public function testFetchLessOrEqual()
    {
        $condition = MyFetchType::condition();
        $condition->myInt = new \Good\Manners\Condition\LessOrEqual(5);

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $ref = new OtherType();
        $ref->yourInt = 90;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new OtherType();
        $ref->yourInt = 80;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    public function testFetchGreaterThan()
    {
        $condition = MyFetchType::condition();
        $condition->myInt = new \Good\Manners\Condition\GreaterThan(5);

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ref = new OtherType();
        $ref->yourInt = 40;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    public function testFetchGreaterOrEqual()
    {
        $condition = MyFetchType::condition();
        $condition->myInt = new \Good\Manners\Condition\GreaterOrEqual(5);

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new OtherType();
        $ref->yourInt = 80;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ref = new OtherType();
        $ref->yourInt = 40;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    public function testFetchEqualTo()
    {
        $condition = MyFetchType::condition();
        $condition->myInt = 5;

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new OtherType();
        $ref->yourInt = 80;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    public function testFetchNotEqualTo()
    {
        $condition = MyFetchType::condition();
        $condition->myInt = new \Good\Manners\Condition\NotEqualTo(5);

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $ref = new OtherType();
        $ref->yourInt = 90;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ref = new OtherType();
        $ref->yourInt = 40;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    /**
     * @depends testFetchEqualTo
     */
    public function testFetchEqualToReference()
    {
        // First, we get the referenced object
        // (we have to do this to get the id of the object,
        //  which is used when we compare to the specific reference,
        //  which is exactly what the whole point of this test is)
        $condition = OtherType::condition();
        $condition->yourInt = 80;

        $results = $this->storage->fetchAll($condition);

        $referenced = $results->getNext();

        // Then, we get the result with that reference
        $condition = MyFetchType::condition();
        $condition->myOtherType = $referenced;

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new OtherType();
        $ref->yourInt = 80;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    /**
     * @depends testFetchLessThan
     * @depends testFetchGreaterThan
     */
    public function testFetchAndOnStorableCondition()
    {
        $greater = MyFetchType::condition();
        $greater->myInt = new \Good\Manners\Condition\GreaterThan(4);

        $less = MyFetchType::condition();
        $less->myInt = new \Good\Manners\Condition\LessThan(10);

        $and = new \Good\Manners\Condition\AndCondition($less, $greater);

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($and, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new OtherType();
        $ref->yourInt = 80;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ref = new OtherType();
        $ref->yourInt = 40;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    public function testFetchAndOnPrimitiveCondition()
    {
       $greater = new \Good\Manners\Condition\GreaterThan(4);
       $less = new \Good\Manners\Condition\LessThan(10);
       $and = new \Good\Manners\Condition\AndCondition($less, $greater);

       $condition = MyFetchType::condition();
       $condition->myInt = $and;

       $resolver = new MyFetchTypeResolver();
       $resolver->resolveMyOtherType();
       $results = $this->storage->fetchAll($condition, $resolver);

       $expectedResults = array();

       $ins = new MyFetchType();
       $ins->myInt = 5;
       $ins->myFloat = null;
       $ins->myText = "Five";
       $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
       $ref = new OtherType();
       $ref->yourInt = 80;
       $ins->myOtherType = $ref;
       $ins->myCircular = null;
       $expectedResults[] = $ins;

       $ins = new MyFetchType();
       $ins->myInt = 8;
       $ins->myFloat = 10.10;
       $ins->myText = null;
       $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
       $ref = new OtherType();
       $ref->yourInt = 40;
       $ins->myOtherType = $ref;
       $ins->myCircular = null;
       $expectedResults[] = $ins;

       foreach ($results as $type)
       {
           $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

           array_splice($expectedResults, $pos, 1);
       }

       $this->assertSame(array(), $expectedResults);
   }

   /**
    * @depends testFetchLessThan
    * @depends testFetchGreaterThan
    */
   public function testFetchAndOnReferenceProperty()
   {
       $greater = OtherType::condition();
       $greater->yourInt = new \Good\Manners\Condition\GreaterThan(75);

       $less = OtherType::condition();
       $less->yourInt = new \Good\Manners\Condition\LessThan(85);

       $and = new \Good\Manners\Condition\AndCondition($less, $greater);

       $condition = MyFetchType::condition();
       $condition->myOtherType = $and;

       $resolver = new MyFetchTypeResolver();
       $resolver->resolveMyOtherType();
       $results = $this->storage->fetchAll($condition, $resolver);

       $expectedResults = array();

       $ins = new MyFetchType();
       $ins->myInt = 5;
       $ins->myFloat = null;
       $ins->myText = "Five";
       $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
       $ref = new OtherType();
       $ref->yourInt = 80;
       $ins->myOtherType = $ref;
       $ins->myCircular = null;
       $expectedResults[] = $ins;

       foreach ($results as $type)
       {
           $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

           array_splice($expectedResults, $pos, 1);
       }

       $this->assertSame(array(), $expectedResults);
   }

   /**
    * @depends testFetchLessThan
    * @depends testFetchGreaterThan
    */
   public function testFetchOrOnReferenceProperty()
   {
       $greater = OtherType::condition();
       $greater->yourInt = new \Good\Manners\Condition\GreaterThan(85);

       $less = OtherType::condition();
       $less->yourInt = new \Good\Manners\Condition\LessThan(10);

       $or = new \Good\Manners\Condition\OrCondition($less, $greater);

       $condition = MyFetchType::condition();
       $condition->myOtherType = $or;

       $resolver = new MyFetchTypeResolver();
       $resolver->resolveMyOtherType();
       $results = $this->storage->fetchAll($condition, $resolver);

       $expectedResults = array();

       $ins = new MyFetchType();
       $ins->myInt = null;
       $ins->myFloat = 20.20;
       $ins->myText = "Twenty";
       $ins->myDatetime = null;
       $ref = new OtherType();
       $ref->yourInt = 5;
       $ins->myOtherType = $ref;
       $ins->myCircular = null;
       $expectedResults[] = $ins;

       $ins = new MyFetchType();
       $ins->myInt = 4;
       $ins->myFloat = 4.4;
       $ins->myText = "Four";
       $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
       $ref = new OtherType();
       $ref->yourInt = 90;
       $ins->myOtherType = $ref;
       $ins->myCircular = null;
       $expectedResults[] = $ins;

       foreach ($results as $type)
       {
           $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

           array_splice($expectedResults, $pos, 1);
       }

       $this->assertSame(array(), $expectedResults);
   }

    /**
     * @depends testFetchLessThan
     * @depends testFetchGreaterThan
     */
    public function testFetchOrOnStorableCondition()
    {
        $less = MyFetchType::condition();
        $less->myInt = new \Good\Manners\Condition\LessThan(5);

        $greater = MyFetchType::condition();
        $greater->myInt = new \Good\Manners\Condition\GreaterThan(8);

        $or = new \Good\Manners\Condition\OrCondition($less, $greater);

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($or, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $ref = new OtherType();
        $ref->yourInt = 90;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    public function testFetchOrOnPrimitiveCondition()
    {
       $less = new \Good\Manners\Condition\LessThan(5);
       $greater = new \Good\Manners\Condition\GreaterThan(8);
       $or = new \Good\Manners\Condition\OrCondition($less, $greater);

       $condition = MyFetchType::condition();
       $condition->myInt = $or;

       $resolver = new MyFetchTypeResolver();
       $resolver->resolveMyOtherType();
       $results = $this->storage->fetchAll($condition, $resolver);

       $expectedResults = array();

       $ins = new MyFetchType();
       $ins->myInt = 4;
       $ins->myFloat = 4.4;
       $ins->myText = "Four";
       $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
       $ref = new OtherType();
       $ref->yourInt = 90;
       $ins->myOtherType = $ref;
       $ins->myCircular = null;
       $expectedResults[] = $ins;

       $ins = new MyFetchType();
       $ins->myInt = 10;
       $ins->myFloat = 10.10;
       $ins->myText = "Ten";
       $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
       $ins->myOtherType = null;
       $ins->myCircular = null;
       $expectedResults[] = $ins;

       foreach ($results as $type)
       {
           $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

           array_splice($expectedResults, $pos, 1);
       }

       $this->assertSame(array(), $expectedResults);
   }

    public function testFetchReferenceIsNull()
    {
        $condition = MyFetchType::condition();
        $condition->myOtherType = null;

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    public function testFetchReferenceIsNotNull()
    {
        $condition = MyFetchType::condition();
        $condition->myOtherType = new \Good\Manners\Condition\NotEqualTo(null);

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $ref = new OtherType();
        $ref->yourInt = 90;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new OtherType();
        $ref->yourInt = 80;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ref = new OtherType();
        $ref->yourInt = 40;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new OtherType();
        $ref->yourInt = 5;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    /**
     * @depends testFetchLessThan
     */
    public function testFetchByPropertyOfReference()
    {
        $condition = MyFetchType::condition();
        $condition->myOtherType->yourInt = new \Good\Manners\Condition\LessThan(85);

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new OtherType();
        $ref->yourInt = 80;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ref = new OtherType();
        $ref->yourInt = 40;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new OtherType();
        $ref->yourInt = 5;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    /**
     * @depends testFetchGreaterThan
     * @depends testFetchByPropertyOfReference
     */
    public function testFetchByTwoValuesInOneCondition()
    {
        $condition = MyFetchType::condition();
        $condition->myInt = new \Good\Manners\Condition\GreaterThan(4);
        $condition->myOtherType->yourInt = new \Good\Manners\Condition\GreaterThan(45);

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new OtherType();
        $ref->yourInt = 80;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    /**
     * @depends testFetchGreaterThan
     */
    public function testFetchByFloat()
    {
        $condition = MyFetchType::condition();
        $condition->myFloat = new \Good\Manners\Condition\GreaterThan(6.0);

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ref = new OtherType();
        $ref->yourInt = 40;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new OtherType();
        $ref->yourInt = 5;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    /**
     * @depends testFetchEqualTo
     */
    public function testFetchByText()
    {
        $condition = MyFetchType::condition();
        $condition->myText = "Twenty";

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new OtherType();
        $ref->yourInt = 5;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    /**
     * @depends testFetchGreaterThan
     */
    public function testFetchByDatetime()
    {
        $condition = MyFetchType::condition();
        $condition->myDatetime = new \Good\Manners\Condition\GreaterThan(new DateTimeImmutable('2006-06-06'));

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ref = new OtherType();
        $ref->yourInt = 40;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    /**
     * @depends testFetchEqualTo
     */
    public function testFetchByIntIsNull()
    {
        $condition = MyFetchType::condition();
        $condition->myInt = null;

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new OtherType();
        $ref->yourInt = 5;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    /**
     * @depends testFetchEqualTo
     */
    public function testFetchByFloatIsNull()
    {
        $condition = MyFetchType::condition();
        $condition->myFloat = null;

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new OtherType();
        $ref->yourInt = 80;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    /**
     * @depends testFetchEqualTo
     */
    public function testFetchByTextIsNull()
    {
        $condition = MyFetchType::condition();
        $condition->myText = null;

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ref = new OtherType();
        $ref->yourInt = 40;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    /**
     * @depends testFetchEqualTo
     */
    public function testFetchByDatetimeIsNull()
    {
        $condition = MyFetchType::condition();
        $condition->myDatetime = null;

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new OtherType();
        $ref->yourInt = 5;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    /**
     * @depends testFetchNotEqualTo
     */
    public function testFetchByIntIsNotNull()
    {
        $condition = MyFetchType::condition();
        $condition->myInt = new \Good\Manners\Condition\NotEqualTo(null);

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $ref = new OtherType();
        $ref->yourInt = 90;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new OtherType();
        $ref->yourInt = 80;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ref = new OtherType();
        $ref->yourInt = 40;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    /**
     * @depends testFetchNotEqualTo
     */
    public function testFetchByFloatIsNotNull()
    {
        $condition = MyFetchType::condition();
        $condition->myFloat = new \Good\Manners\Condition\NotEqualTo(null);

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $ref = new OtherType();
        $ref->yourInt = 90;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ref = new OtherType();
        $ref->yourInt = 40;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new OtherType();
        $ref->yourInt = 5;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    /**
     * @depends testFetchNotEqualTo
     */
    public function testFetchByTextIsNotNull()
    {
        $condition = MyFetchType::condition();
        $condition->myText = new \Good\Manners\Condition\NotEqualTo(null);

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $ref = new OtherType();
        $ref->yourInt = 90;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new OtherType();
        $ref->yourInt = 80;
        $ins->myOtherType = $ref;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new OtherType();
        $ref->yourInt = 5;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    /**
     * @depends testFetchNotEqualTo
     */
    public function testFetchByDatetimeIsNotNull()
    {
        $condition = MyFetchType::condition();
        $condition->myDatetime = new \Good\Manners\Condition\NotEqualTo(null);

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $ref = new OtherType();
        $ref->yourInt = 90;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new OtherType();
        $ref->yourInt = 80;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ref = new OtherType();
        $ref->yourInt = 40;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    /**
     * @depends testFetchAll
     */
    public function testFetchSortedAscending()
    {
        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $resolver->orderByMyIntAsc();
        $results = $this->storage->fetchAll($resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new OtherType();
        $ref->yourInt = 5;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $type = $results->getNext();
        $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $ref = new OtherType();
        $ref->yourInt = 90;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $type = $results->getNext();
        $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new OtherType();
        $ref->yourInt = 80;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $type = $results->getNext();
        $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ref = new OtherType();
        $ref->yourInt = 40;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $type = $results->getNext();
        $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $type = $results->getNext();
        $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

        $this->assertSame(null, $results->getNext());
    }

    /**
     * @depends testFetchAll
     */
    public function testFetchSortedDescending()
    {
        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $resolver->orderByMyIntDesc();
        $results = $this->storage->fetchAll($resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $type = $results->getNext();
        $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ref = new OtherType();
        $ref->yourInt = 40;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $type = $results->getNext();
        $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new OtherType();
        $ref->yourInt = 80;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $type = $results->getNext();
        $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $ref = new OtherType();
        $ref->yourInt = 90;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $type = $results->getNext();
        $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new OtherType();
        $ref->yourInt = 5;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $type = $results->getNext();
        $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

        $this->assertSame(null, $results->getNext());
    }

    /**
     * @depends testFetchAll
     * @depends testFetchSortedAscending
     * @depends testFetchSortedDescending
     */
    public function testFetchDoubleSorted()
    {
        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $resolver->orderByMyFloatAsc();
        $resolver->orderByMyIntDesc();
        $results = $this->storage->fetchAll($resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new OtherType();
        $ref->yourInt = 80;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $type = $results->getNext();
        $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $ref = new OtherType();
        $ref->yourInt = 90;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $type = $results->getNext();
        $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $type = $results->getNext();
        $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ref = new OtherType();
        $ref->yourInt = 40;
        $ins->myOtherType = $ref;
        $expectedResults[] = $ins;

        $type = $results->getNext();
        $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new OtherType();
        $ref->yourInt = 5;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $type = $results->getNext();
        $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

        $expectedResults = array();

        $this->assertSame(null, $results->getNext());
    }

    /**
     * @depends testFetchAll
     */
    public function testFetchAllUnresolvedReference()
    {
        $resolver = new MyFetchTypeResolver();
        $results = $this->storage->fetchAll($resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
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
     * @depends testFetchAll
     */
    public function testFetchCircularReference()
    {
        // First, we need to create a circular reference:
        $resolver = new MyFetchTypeResolver();
        $resolver->orderByMyIntAsc();
        $results = $this->storage->fetchAll($resolver);
        foreach ($results as $type)
        {
            if ($type->myInt == 4)
            {
                $ref = $type;
            }
            else if ($type->myInt == 10)
            {
                $ref->myCircular = $type;
                $type->myCircular = $ref;
            }
        }
        $this->storage->flush();

        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyCircular();

        $results = $this->storage->fetchAll($resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $ins->myOtherType = null;
        $expectedResults[] = $ins;
        $int4 = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ins->myOtherType = null;
        $ins->myCircular = $int4;
        $expectedResults[] = $ins;
        $int4->myCircular = $ins;

        $ins = new MyFetchType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        foreach ($results as $type)
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
     * @depends testFetchAll
     */
    public function testNestedForeachOnStorableresults()
    {
        $resolver = new MyFetchTypeResolver();
        $resolver->resolveMyOtherType();
        $resolver->orderByMyIntDesc();
        $results = $this->storage->fetchAll($resolver);

        $expectedResults = array();

        $ins = new MyFetchType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ins->myOtherType = null;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ref = new OtherType();
        $ref->yourInt = 40;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new OtherType();
        $ref->yourInt = 80;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $ref = new OtherType();
        $ref->yourInt = 90;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $ins = new MyFetchType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new OtherType();
        $ref->yourInt = 5;
        $ins->myOtherType = $ref;
        $ins->myCircular = null;
        $expectedResults[] = $ins;

        $exp1 = $expectedResults;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $exp1);

            array_splice($exp1, $pos, 1);

            $exp2 = $expectedResults;

            foreach ($results as $type)
            {
                $pos = $this->assertContainsAndReturnIndex_specific($type, $exp2);

                array_splice($exp2, $pos, 1);
            }

            $this->assertSame(array(), $exp2);
        }

        $this->assertSame(array(), $exp1);
    }
}

?>
