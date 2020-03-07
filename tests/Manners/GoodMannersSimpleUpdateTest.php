<?php

/**
 * @runTestsInSeparateProcesses
 */
abstract class GoodMannersSimpleUpdateTest extends \PHPUnit\Framework\TestCase
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
        file_put_contents(dirname(__FILE__) . '/../testInputFiles/SimpleUpdateType.datatype',
                                                                            "datatype SimpleUpdateType\n" .
                                                                            "{" .
                                                                            "   int myInt;\n" .
                                                                            "   float myFloat;\n".
                                                                            "   text myText;\n" .
                                                                            "   datetime myDatetime;\n" .
                                                                            '   "AnotherType" myReference;' . "\n" .
                                                                            "}\n");

        file_put_contents(dirname(__FILE__) . '/../testInputFiles/AnotherType.datatype',
                                                                            "datatype AnotherType { int yourInt; }");

        $rolemodel = new \Good\Rolemodel\Rolemodel();
        $schema = $rolemodel->createSchema(array(dirname(__FILE__) . '/../testInputFiles/SimpleUpdateType.datatype',
                                                 dirname(__FILE__) . '/../testInputFiles/AnotherType.datatype'));

        $service = new \Good\Service\Service();
        $service->compile(array(new \Good\Manners\Modifier\Storable()), $schema, dirname(__FILE__) . '/../generated/');

        require dirname(__FILE__) . '/../generated/SimpleUpdateType.datatype.php';
        require dirname(__FILE__) . '/../generated/AnotherType.datatype.php';

        require dirname(__FILE__) . '/../generated/SimpleUpdateTypeResolver.php';
        require dirname(__FILE__) . '/../generated/AnotherTypeResolver.php';
    }

    public static function _tearDownAfterClass()
    {
        unlink(dirname(__FILE__) . '/../testInputFiles/SimpleUpdateType.datatype');
        unlink(dirname(__FILE__) . '/../testInputFiles/AnotherType.datatype');
        unlink(dirname(__FILE__) . '/../generated/SimpleUpdateType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/AnotherType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/SimpleUpdateTypeResolver.php');
        unlink(dirname(__FILE__) . '/../generated/AnotherTypeResolver.php');
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
        $this->truncateTable('simpleupdatetype');
        $this->truncateTable('anothertype');

        $storage = $this->getNewStorage();

        $ins = new SimpleUpdateType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $ref = new AnotherType();
        $ref->yourInt = 50;
        $ins->myReference = $ref;
        $storage->insert($ins);

        $ins = new SimpleUpdateType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new AnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $storage->insert($ins);

        $ins = new SimpleUpdateType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ref = new AnotherType();
        $ref->yourInt = 30;
        $ins->myReference = $ref;
        $storage->insert($ins);

        $ins = new SimpleUpdateType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ref = new AnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $storage->insert($ins);

        $ins = new SimpleUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new AnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $storage->insert($ins);

        $storage->flush();

        // new Storage, so communication will have to go through data storage
        $this->storage1 = $this->getNewStorage();
        $this->storage2 = $this->getNewStorage();
    }

    public function tearDown(): void
    {
        // Just doing this already to make sure the deconstructor will hasve
        // side-effects at an unspecified moment...
        // (at which point the database will probably be in a wrong state for this)
        $this->storage1->flush();
        $this->storage2->flush();

        // this should be handled through the GoodManners API once that is implemented
        $this->truncateTable('simpleupdatetype');
        $this->truncateTable('anothertype');

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
                  $hay->myReference->yourInt == $needle->myReference->yourInt)))
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
        $resolver = new SimpleUpdateTypeResolver();
        $resolver->resolveMyReference();
        $results = $this->storage2->fetchAll($resolver);

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expected);

            array_splice($expected, $pos, 1);
        }

        $this->assertSame(array(), $expected);
    }

    public function testSimpleUpdate()
    {
        $resolver = new SimpleUpdateTypeResolver();
        $resolver->resolveMyReference();
        $results = $this->storage1->fetchAll($resolver);

        foreach ($results as $type)
        {
            $type->myInt = 2;
            $type->myFloat = 1.1;
            $type->myText = "Zero";
            $type->myDatetime = new DateTimeImmutable('1999-12-31');
        }

        $this->storage1->flush();

        $expectedResults = array();

        $ins = new SimpleUpdateType();
        $ins->myInt = 2;
        $ins->myFloat = 1.1;
        $ins->myText = "Zero";
        $ins->myDatetime = new DateTimeImmutable('1999-12-31');
        $ins->myReference = new AnotherType();
        $ins->myReference->yourInt = 50;
        $expectedResults[] = $ins;

        $ins = new SimpleUpdateType();
        $ins->myInt = 2;
        $ins->myFloat = 1.1;
        $ins->myText = "Zero";
        $ins->myDatetime = new DateTimeImmutable('1999-12-31');
        $ins->myReference = new AnotherType();
        $ins->myReference->yourInt = 40;
        $expectedResults[] = $ins;

        $ins = new SimpleUpdateType();
        $ins->myInt = 2;
        $ins->myFloat = 1.1;
        $ins->myText = "Zero";
        $ins->myDatetime = new DateTimeImmutable('1999-12-31');
        $ins->myReference = new AnotherType();
        $ins->myReference->yourInt = 30;
        $expectedResults[] = $ins;

        $ins = new SimpleUpdateType();
        $ins->myInt = 2;
        $ins->myFloat = 1.1;
        $ins->myText = "Zero";
        $ins->myDatetime = new DateTimeImmutable('1999-12-31');
        $ins->myReference = new AnotherType();
        $ins->myReference->yourInt = 20;
        $expectedResults[] = $ins;

        $ins = new SimpleUpdateType();
        $ins->myInt = 2;
        $ins->myFloat = 1.1;
        $ins->myText = "Zero";
        $ins->myDatetime = new DateTimeImmutable('1999-12-31');
        $ins->myReference = new AnotherType();
        $ins->myReference->yourInt = 10;
        $expectedResults[] = $ins;

        $this->checkResults($expectedResults);
    }

    public function testSimpleUpdateSetToNull()
    {
        $resolver = new SimpleUpdateTypeResolver();
        $resolver->resolveMyReference();
        $results = $this->storage1->fetchAll($resolver);

        foreach ($results as $type)
        {
            if ($type->myInt == 5)
            {
                $type->myInt = null;
                $type->myText = null;
            }

            if ($type->myInt == 10)
            {
                $type->myFloat = null;
                $type->myDatetime = null;
            }
        }

        $this->storage1->flush();

        $expectedResults = array();

        $ins = new SimpleUpdateType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $ref = new AnotherType();
        $ref->yourInt = 50;
        $ins->myReference = $ref;
        $expectedResults[] = $ins;

        $ins = new SimpleUpdateType();
        $ins->myInt = null;
        $ins->myFloat = null;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new AnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $expectedResults[] = $ins;

        $ins = new SimpleUpdateType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ref = new AnotherType();
        $ref->yourInt = 30;
        $ins->myReference = $ref;
        $expectedResults[] = $ins;

        $ins = new SimpleUpdateType();
        $ins->myInt = 10;
        $ins->myFloat = null;
        $ins->myText = "Ten";
        $ins->myDatetime = null;
        $ref = new AnotherType();
        $ref->yourInt = 20;
        $ins->myReference = $ref;
        $expectedResults[] = $ins;

        $ins = new SimpleUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new AnotherType();
        $ref->yourInt = 10;
        $ins->myReference = $ref;
        $expectedResults[] = $ins;

        $this->checkResults($expectedResults);
    }

    public function testSimpleUpdateReferences()
    {
        $resolver = new SimpleUpdateTypeResolver();
        $resolver->resolveMyReference();
        $resolver->orderByMyIntAsc();
        $results = $this->storage1->fetchAll($resolver);

        $ref = null;

        foreach ($results as $type)
        {
            if ($type->myInt == 8)
            {
                $ref = $type->myReference;
                $type->myReference = null;
            }

            if ($type->myInt == 10)
            {
                $type->myReference = $ref;
            }

            if ($type->myFloat == 20.20)
            {
                $myref = new AnotherType();
                $myref->yourInt = 144;
                $type->myReference = $myref;
            }
        }

        $this->storage1->flush();

        $expectedResults = array();

        $ins = new SimpleUpdateType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $ref = new AnotherType();
        $ref->yourInt = 50;
        $ins->myReference = $ref;
        $expectedResults[] = $ins;

        $ins = new SimpleUpdateType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $ref = new AnotherType();
        $ref->yourInt = 40;
        $ins->myReference = $ref;
        $expectedResults[] = $ins;

        $ins = new SimpleUpdateType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $ins->myReference = null;
        $expectedResults[] = $ins;

        $ins = new SimpleUpdateType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $ref = new AnotherType();
        $ref->yourInt = 30;
        $ins->myReference = $ref;
        $expectedResults[] = $ins;

        $ins = new SimpleUpdateType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $ref = new AnotherType();
        $ref->yourInt = 144;
        $ins->myReference = $ref;
        $expectedResults[] = $ins;

        $this->checkResults($expectedResults);
    }
}

?>
