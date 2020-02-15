<?php

/**
 * @runTestsInSeparateProcesses
 */
abstract class GoodMannersStoredCollectionTest extends \PHPUnit\Framework\TestCase
{
    abstract public function getNewStorage();
    abstract public function getNewDb();
    // this function should be removed, but is used for clearing the database at the moment
    abstract public function truncateTable($table);

    private function assertNoExceptions()
    {
        $this->assertTrue(true);
    }

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
        file_put_contents(dirname(__FILE__) . '/../testInputFiles/CollectionType.datatype',
                                                                            "datatype CollectionType\n" .
                                                                            "{" .
                                                                            "   int someInt;\n" .
                                                                            "   datetime[] myDatetimes;\n" .
                                                                            "   float[] myFloats;\n" .
                                                                            "   int[] myInts;\n" .
                                                                            "   \"CollectionType\"[] myReferences;\n" .
                                                                            "   text[] myTexts;\n" .
                                                                            "}\n");

        $rolemodel = new \Good\Rolemodel\Rolemodel();
        $schema = $rolemodel->createSchema(array(dirname(__FILE__) . '/../testInputFiles/CollectionType.datatype'));

        $service = new \Good\Service\Service();
        $service->compile(array(new \Good\Manners\Modifier\Storable()), $schema, dirname(__FILE__) . '/../generated/');

        require dirname(__FILE__) . '/../generated/CollectionType.datatype.php';
        require dirname(__FILE__) . '/../generated/CollectionTypeResolver.php';
    }

    public static function _tearDownAfterClass()
    {
        return;

        unlink(dirname(__FILE__) . '/../testInputFiles/CollectionType.datatype');
        unlink(dirname(__FILE__) . '/../generated/CollectionType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/CollectionTypeResolver.php');
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
        $this->truncateTable('collectiontype');
        $this->truncateTable('collectiontype_myints');
        $this->truncateTable('collectiontype_myfloats');
        $this->truncateTable('collectiontype_mytexts');
        $this->truncateTable('collectiontype_mydatetimes');
        $this->truncateTable('collectiontype_myreferences');

        $this->storage = $this->getNewStorage();
    }

    public function tearDown(): void
    {
        // Just doing this already to make sure the deconstructor will hasve
        // side-effects at an unspecified moment...
        // (at which point the database will probably be in a wrong state for this)
        $this->storage->flush();

        // this should be handled through the GoodManners API once that is implemented
        $this->truncateTable('collectiontype');
        $this->truncateTable('collectiontype_myints');
        $this->truncateTable('collectiontype_myfloats');
        $this->truncateTable('collectiontype_mytexts');
        $this->truncateTable('collectiontype_mydatetimes');
        $this->truncateTable('collectiontype_myreferences');

        $this->_tearDownAfterClass();
    }

    public function testInsertCollection()
    {
        $myCollectionType = new CollectionType();
        $myCollectionType->someInt = 4;

        $myCollectionType->myInts->add(2);
        $myCollectionType->myInts->add(4);

        $myCollectionType->myFloats->add(2.2);
        $myCollectionType->myFloats->add(4.4);

        $myCollectionType->myTexts->add("abc");
        $myCollectionType->myTexts->add("def");

        $myCollectionType->myDatetimes->add(new DateTimeImmutable('2001-01-01'));
        $myCollectionType->myDatetimes->add(new DateTimeImmutable('2002-02-02'));

        $reference = new CollectionType();
        $reference->someInt = 1;

        $myCollectionType->myReferences->add($reference);
        $myCollectionType->myReferences->add($myCollectionType);

        $this->storage->insert($myCollectionType);

        $this->storage->flush();

        $this->assertNoExceptions();
    }
}

?>
