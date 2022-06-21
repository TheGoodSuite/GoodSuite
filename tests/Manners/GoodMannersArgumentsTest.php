<?php

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * Tests the different sets of arguments you should and shouldn't be able
 * to pass to the `fetchAll()` method.
 */
abstract class GoodMannersArgumentsTest extends \PHPUnit\Framework\TestCase
{
    abstract public function getNewStorage();
    abstract public function getNewDb();
    // this function should be removed, but is used for clearing the database at the moment
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

        $service = new \Good\Service\Service([
            "modifiers" => [new \Good\Manners\Modifier\Storable()],
            "inputDir" => dirname(__FILE__) . '/../testInputFiles/GoodManners/GoodMannersArgumentsTest',
            "outputDir" => dirname(__FILE__) . '/../generated/'
        ]);

        $service->load();
    }

    public static function _tearDownAfterClass()
    {
        unlink(dirname(__FILE__) . '/../generated/ArgumentsType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/ArgumentsTypeResolver.php');
        unlink(dirname(__FILE__) . '/../generated/ArgumentsTypeCondition.php');
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
        $this->truncateTable('ArgumentsType');

        $this->storage = $this->getNewStorage();
    }

    public function tearDown(): void
    {
        // Just doing this already to make sure the deconstructor will hasve
        // side-effects at an unspecified moment...
        // (at which point the database will probably be in a wrong state for this)
        $this->storage->flush();

        // this should be handled through the GoodManners API once that is implemented
        $this->truncateTable('argumentstype');

        $this->_tearDownAfterClass();
    }

    public function populateDatabase()
    {
        $storage = $this->getNewStorage();

        $ins = new ArgumentsType();
        $ins->myInt = 4;
        $ins->myFloat = 4.4;
        $ins->myText = "Four";
        $ins->myDatetime = new \DateTimeImmutable('2004-04-04');
        $storage->insert($ins);

        $ins = new ArgumentsType();
        $ins->myInt = 5;
        $ins->myFloat = 5.5;
        $ins->myText = "Five";
        $ins->myDatetime = new \DateTimeImmutable('2005-05-05');
        $storage->insert($ins);

        $ins = new ArgumentsType();
        $ins->myInt = 6;
        $ins->myFloat = 6.6;
        $ins->myText = "Six";
        $ins->myDatetime = new \DateTimeImmutable('2006-06-06');
        $storage->insert($ins);

        $storage->flush();
    }

    public function testGetCollectionConditionAndResolver()
    {
        $this->populateDatabase();

        $condition = ArgumentsType::condition();
        $condition->myInt = new \Good\Manners\Condition\GreaterThan(4);

        $resolver = ArgumentsType::resolver();
        $resolver->orderByMyIntDesc();

        $results = $this->storage->fetchAll($condition, $resolver);

        $result = $results->getNext();
        $this->assertSame(6, $result->myInt);

        $result = $results->getNext();
        $this->assertSame(5, $result->myInt);

        $this->assertSame(null, $results->getNext());
    }

    public function testGetCollectionConditionOnly()
    {
        $this->populateDatabase();

        $condition = ArgumentsType::condition();
        $condition->myInt = new \Good\Manners\Condition\GreaterThan(4);

        $results = $this->storage->fetchAll($condition);

        $result = $results->getNext();
        $result = $results->getNext();
        $this->assertSame(null, $results->getNext());
    }

    public function testGetCollectionResolverOnly()
    {
        $this->populateDatabase();

        $resolver = ArgumentsType::resolver();
        $resolver->orderByMyIntDesc();

        $results = $this->storage->fetchAll($resolver);

        $result = $results->getNext();
        $this->assertSame(6, $result->myInt);

        $result = $results->getNext();
        $this->assertSame(5, $result->myInt);

        $result = $results->getNext();
        $this->assertSame(4, $result->myInt);

        $this->assertSame(null, $results->getNext());
    }

    public function testGetCollectionExceptionIncorrectSingleArgument()
    {
        $this->expectException("InvalidArgumentException");

        $results = $this->storage->fetchAll("a");
    }

    public function testGetCollectionExceptionTwoResolvers()
    {
        $this->expectException("InvalidArgumentException");

        $results = $this->storage->fetchAll(ArgumentsType::resolver(), ArgumentsType::resolver());
    }
}

?>
