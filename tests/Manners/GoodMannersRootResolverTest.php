<?php

require_once dirname(__FILE__) . '/../TestHelper.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * Tests that when you provide a non-root resolver, the root of that
 * resolver will be used.
 * The reason for this is that it provides support for this one-liner:
 * `$storage->fetchAll(Type::resolver()->resolveSomeReference());`
 */
abstract class GoodMannersRootResolverTest extends \PHPUnit\Framework\TestCase
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

        TestHelper::cleanGeneratedFiles();

        $service = new \Good\Service\Service([
            "modifiers" => [new \Good\Manners\Modifier\Storable()],
            "inputDir" => dirname(__FILE__) . '/../testInputFiles/GoodManners/GoodMannersRootResolverTest',
            "outputDir" => dirname(__FILE__) . '/../generated/'
        ]);

        $service->load();
    }

    public static function _tearDownAfterClass()
    {
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

    public function populateDatabase()
    {

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

        $storage->flush();
    }

    public function testGetCollectionConditionAndResolver()
    {
        $this->populateDatabase();

        $results = $this->storage->fetchAll(MyFetchType::resolver()->resolveMyOtherType());

        $result = $results->getNext();
        $this->assertSame(90, $result->myOtherType->yourInt);
    }
}

?>
