<?php

require_once dirname(__FILE__) . '/../TestHelper.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class GoodMannersResolverChainingTest extends \PHPUnit\Framework\TestCase
{
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
            "inputDir" => dirname(__FILE__) . '/../testInputFiles/GoodManners/GoodMannersResolverChainingTest',
            "outputDir" => dirname(__FILE__) . '/../generated/'
        ]);

        $service->load();
    }

    public static function _tearDownAfterClass()
    {
        unlink(dirname(__FILE__) . '/../generated/PersistenceType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/PersistenceTypeResolver.php');
        unlink(dirname(__FILE__) . '/../generated/PersistenceTypeCondition.php');
        unlink(dirname(__FILE__) . '/../generated/GeneratedBaseClass.php');

        if (ini_get('zend.enable_gc'))
        {
            gc_enable();
        }
    }

    public function setUp(): void
    {
        $this->_setUpBeforeClass();
    }

    public function tearDown(): void
    {
        $this->_tearDownAfterClass();
    }

    public function testOrderAscChaining()
    {
        $resolver = PersistenceType::resolver();

        $result = $resolver->orderByMyIntAsc();

        $this->assertSame($resolver, $result);
    }

    public function testOrderDescChaining()
    {
        $resolver = PersistenceType::resolver();

        $result = $resolver->orderByMyIntDesc();

        $this->assertSame($resolver, $result);
    }
}

?>
