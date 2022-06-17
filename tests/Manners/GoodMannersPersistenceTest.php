<?php

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
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

        $service = new \Good\Service\Service([
            "modifiers" => [new \Good\Manners\Modifier\Storable()],
            "inputDir" => dirname(__FILE__) . '/../testInputFiles/GoodManners/GoodMannersPersistenceTest',
            "outputDir" => dirname(__FILE__) . '/../generated/'
        ]);

        $service->load();
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

        unlink(dirname(__FILE__) . '/../generated/PersistenceType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/PersistenceTypeResolver.php');
        unlink(dirname(__FILE__) . '/../generated/PersistenceTypeCondition.php');
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
        $results = $storage->fetchAll($resolver);

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);

        $this->_tearDown();
    }
}

?>
