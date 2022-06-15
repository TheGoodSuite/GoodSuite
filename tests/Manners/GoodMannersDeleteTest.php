<?php

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
abstract class GoodMannersDeleteTest extends \PHPUnit\Framework\TestCase
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
        file_put_contents(dirname(__FILE__) . '/../testInputFiles/DeleteType.datatype',
                                                                            "datatype DeleteType\n" .
                                                                            "{\n" .
                                                                            "   int myInt;\n" .
                                                                            "   float myFloat;\n".
                                                                            "   text myText;\n" .
                                                                            "   datetime myDatetime;\n" .
                                                                            "}\n" );

        $modifiers = [new \Good\Manners\Modifier\Storable()];

        $service = new \Good\Service\Service();
        $service->autocompile(dirname(__FILE__) . '/../testInputFiles/', dirname(__FILE__) . '/../generated/', $modifiers);
    }

    public static function _tearDownAfterClass()
    {
        unlink(dirname(__FILE__) . '/../testInputFiles/DeleteType.datatype');
        unlink(dirname(__FILE__) . '/../generated/DeleteType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/DeleteTypeResolver.php');
        unlink(dirname(__FILE__) . '/../generated/DeleteTypeCondition.php');
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
        $this->truncateTable('deletetype');

        $storage = $this->getNewStorage();

        $ins = new DeleteType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $storage->insert($ins);

        $ins = new DeleteType();
        $ins->myInt = 5;
        $ins->myFloat = null;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $storage->insert($ins);

        $ins = new DeleteType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $storage->insert($ins);

        $ins = new DeleteType();
        $ins->myInt = 10;
        $ins->myFloat = 10.10;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2010-10-10');
        $storage->insert($ins);

        $ins = new DeleteType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
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
        $this->truncateTable('deletetype');

        $this->_tearDownAfterClass();
    }

    private function array_search_specific($needle, $haystack)
    {
        // this is sort of a array_search
        // (it has to ignore any additional fields, though)
        foreach ($haystack as $key => $hay)
        {
            if ($hay->myInt === $needle->myInt &&
                $hay->myFloat === $needle->myFloat &&
                $hay->myText === $needle->myText &&
                $hay->myDatetime == $needle->myDatetime)
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
        $results = $this->storage2->fetchAll(DeleteType::resolver());

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expected);

            array_splice($expected, $pos, 1);
        }

        $this->assertSame(array(), $expected);
    }

    public function testDelete()
    {
        $results = $this->storage1->fetchAll(DeleteType::resolver());

        foreach ($results as $type)
        {
            if ($type->myInt == 5 || $type->myInt == 10)
            {
                $type->delete();
            }
        }

        $this->storage1->flush();

        $expectedResults = array();

        $ins = new DeleteType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $expectedResults[] = $ins;

        $ins = new DeleteType();
        $ins->myInt = 8;
        $ins->myFloat = 10.10;
        $ins->myText = null;
        $ins->myDatetime = new \DateTimeImmutable('2008-08-08');
        $expectedResults[] = $ins;

        $ins = new DeleteType();
        $ins->myInt = null;
        $ins->myFloat = 20.20;
        $ins->myText = "Twenty";
        $ins->myDatetime = null;
        $expectedResults[] = $ins;

        $this->checkResults($expectedResults);
    }
}

?>
