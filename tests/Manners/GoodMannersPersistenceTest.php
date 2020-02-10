<?php

/**
 * @runTestsInSeparateProcesses
 */
abstract class GoodMannersPersistenceTest extends \PHPUnit\Framework\TestCase
{
    abstract public function getNewStorage();
    // this function should be removed, but is used for clearing the database at the moment
    abstract public function getNewDb();
    abstract public function truncateTable($table);

    public function _setUpEachHalf()
    {
        // Garbage collector causes segmentation fault, so we disable
        // for the duration of the test case
        gc_disable();
        file_put_contents(dirname(__FILE__) . '/../testInputFiles/PersistenceType.datatype',
                                                                            "datatype PersistenceType\n" .
                                                                            "{" .
                                                                            "   int myInt;\n" .
                                                                            "   float myFloat;\n".
                                                                            "   text myText;\n" .
                                                                            "   datetime myDatetime;\n" .
                                                                            "}\n");

        $rolemodel = new \Good\Rolemodel\Rolemodel();
        $schema = $rolemodel->createSchema(array(dirname(__FILE__) . '/../testInputFiles/PersistenceType.datatype'));

        $service = new \Good\Service\Service();
        $service->compile(array(new \Good\Manners\Modifier\Storable()), $schema, dirname(__FILE__) . '/../generated/');

        require dirname(__FILE__) . '/../generated/PersistenceType.datatype.php';

        require dirname(__FILE__) . '/../generated/PersistenceTypeResolver.php';
    }


    public function _setUp()
    {
        // just doubling this up (from tearDown) to be sure
        // this should be handled natively once that is implemented
        $this->truncateTable('persistencetype');
    }

    public function _tearDown()
    {
        // this should be handled through the GoodManners API once that is implemented
        $this->truncateTable('persistencetype');

        unlink(dirname(__FILE__) . '/../testInputFiles/PersistenceType.datatype');
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
            if ($hay->myInt == $needle->myInt &&
                $hay->myFloat == $needle->myFloat &&
                $hay->myText == $needle->myText &&
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

        $storage = $this->getNewStorage();

        $ins = new PersistenceType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $storage->insert($ins);

        $ins = new PersistenceType();
        $ins->myInt = 5;
        $ins->myFloat = 8.8;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2012-12-12');
        $storage->insert($ins);

        $expectedResults = array();

        // Actually those two tests are just one test
        // (continued below)
        // This assertion is to make sure it isn't marked as risky
        $this->assertTrue(true);
    }

    /**
     * @depends testInsert
     */
    public function testGet()
    {
        // However, I wanted the two parts to run in different prcoesses
        // and this was an easy way to accomplish that.
        $this->_setUpEachHalf();

        $storage = $this->getNewStorage();

        $expectedResults = array();

        $ins = new PersistenceType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $expectedResults[] = $ins;

        $ins = new PersistenceType();
        $ins->myInt = 5;
        $ins->myFloat = 8.8;
        $ins->myText = "Ten";
        $ins->myDatetime = new \DateTimeImmutable('2012-12-12');
        $expectedResults[] = $ins;

        $resolver = new PersistenceTypeResolver();
        $collection = $storage->getCollection($resolver);

        foreach ($collection as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);

        $this->_tearDown();
    }
}

?>
