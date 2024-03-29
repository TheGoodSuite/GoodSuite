<?php

require_once dirname(__FILE__) . '/../TestHelper.php';

use Good\Manners\Page;
use Good\Manners\Condition\GreaterThan;
use Good\Manners\CollectionCondition\HasA;
use Good\Manners\CollectionCondition\HasOnly;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
abstract class GoodMannersPaginationTest extends \PHPUnit\Framework\TestCase
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

        TestHelper::cleanGeneratedFiles();

        $service = new \Good\Service\Service([
            "modifiers" => [new \Good\Manners\Modifier\Storable()],
            "inputDir" => dirname(__FILE__) . '/../testInputFiles/GoodManners/GoodMannersPaginationTest',
            "outputDir" => dirname(__FILE__) . '/../generated/'
        ]);

        $service->load();
    }

    public static function _tearDownAfterClass()
    {
        unlink(dirname(__FILE__) . '/../generated/PaginationType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/PaginationTypeResolver.php');
        unlink(dirname(__FILE__) . '/../generated/PaginationTypeCondition.php');
        unlink(dirname(__FILE__) . '/../generated/ReferencedByPagination.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/ReferencedByPaginationResolver.php');
        unlink(dirname(__FILE__) . '/../generated/ReferencedByPaginationCondition.php');
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
        $this->truncateTable('paginationtype');
        $this->truncateTable('paginationtype_collectionofints');
        $this->truncateTable('paginationtype_collectionofreferences');
        $this->truncateTable('referencedbypagination');
        $this->truncateTable('referencedbypagination_myints');

        $this->storage = $this->getNewStorage();
    }

    public function tearDown(): void
    {
        // Just doing this already to make sure the deconstructor will hasve
        // side-effects at an unspecified moment...
        // (at which point the database will probably be in a wrong state for this)
        $this->storage->flush();

        // this should be handled through the GoodManners API once that is implemented
        $this->truncateTable('paginationtype');
        $this->truncateTable('paginationtype_collectionofints');
        $this->truncateTable('paginationtype_collectionofreferences');
        $this->truncateTable('referencedbypagination');
        $this->truncateTable('referencedbypagination_myints');

        $this->_tearDownAfterClass();
    }

    public function populateDatabase()
    {
        $storage = $this->getNewStorage();

        $paginationType = new PaginationType();
        $paginationType->myInt = 6;
        $referenced = new ReferencedByPagination();
        $referenced->myText = 'b';
        $referenced->myInts->add(12);
        $referenced->myInts->add(18);
        $paginationType->myReference = $referenced;
        $paginationType->collectionOfInts->add(12);
        $paginationType->collectionOfInts->add(18);
        $referenced = new ReferencedByPagination();
        $referenced->myText = 'six';
        $referenced->myInts->add(6);
        $referenced->myInts->add(12);
        $paginationType->collectionOfReferences->add($referenced);
        $referenced = new ReferencedByPagination();
        $referenced->myText = 'six+';
        $referenced->myInts->add(12);
        $referenced->myInts->add(18);
        $paginationType->collectionOfReferences->add($referenced);

        $storage->insert($paginationType);

        $paginationType = new PaginationType();
        $paginationType->myInt = 2;
        $referenced = new ReferencedByPagination();
        $referenced->myText = 'a';
        $referenced->myInts->add(4);
        $referenced->myInts->add(6);
        $paginationType->myReference = $referenced;
        $paginationType->collectionOfInts->add(4);
        $paginationType->collectionOfInts->add(6);
        $referenced = new ReferencedByPagination();
        $referenced->myText = 'two';
        $referenced->myInts->add(2);
        $referenced->myInts->add(4);
        $paginationType->collectionOfReferences->add($referenced);
        $referenced = new ReferencedByPagination();
        $referenced->myText = 'two+';
        $referenced->myInts->add(4);
        $referenced->myInts->add(6);
        $paginationType->collectionOfReferences->add($referenced);

        $storage->insert($paginationType);

        $paginationType = new PaginationType();
        $paginationType->myInt = 4;
        $referenced = new ReferencedByPagination();
        $referenced->myText = 'c';
        $referenced->myInts->add(8);
        $referenced->myInts->add(12);
        $paginationType->myReference = $referenced;
        $paginationType->collectionOfInts->add(8);
        $paginationType->collectionOfInts->add(12);
        $referenced = new ReferencedByPagination();
        $referenced->myText = 'four';
        $referenced->myInts->add(4);
        $referenced->myInts->add(8);
        $paginationType->collectionOfReferences->add($referenced);
        $referenced = new ReferencedByPagination();
        $referenced->myText = 'four+';
        $referenced->myInts->add(8);
        $referenced->myInts->add(12);
        $paginationType->collectionOfReferences->add($referenced);

        $storage->insert($paginationType);

        $paginationType = new PaginationType();
        $paginationType->myInt = 8;
        $referenced = new ReferencedByPagination();
        $referenced->myText = 'd';
        $referenced->myInts->add(16);
        $referenced->myInts->add(24);
        $paginationType->myReference = $referenced;
        $paginationType->collectionOfInts->add(16);
        $paginationType->collectionOfInts->add(24);
        $referenced = new ReferencedByPagination();
        $referenced->myText = 'eight';
        $referenced->myInts->add(8);
        $referenced->myInts->add(16);
        $paginationType->collectionOfReferences->add($referenced);
        $referenced = new ReferencedByPagination();
        $referenced->myText = 'eight+';
        $referenced->myInts->add(16);
        $referenced->myInts->add(24);
        $paginationType->collectionOfReferences->add($referenced);

        $storage->insert($paginationType);

        $storage->flush();
    }

    public function testSizeOnlyPagination()
    {
        $this->populateDatabase();

        $condition = null;

        $resolver = PaginationType::resolver();
        $resolver->orderByMyIntAsc();

        $page = new Page(2);

        $results = $this->storage->fetchAll($resolver, $page);

        $i = 0;
        foreach($results as $result)
        {
            if ($i === 0)
            {
                $this->assertSame(2, $result->myInt);
            }
            else if ($i === 1)
            {
                $this->assertSame(4, $result->myInt);
            }

            $i++;
        }

        $this->assertSame($i, 2);
    }

    public function testSizeAndStartAtPagination()
    {
        $this->populateDatabase();

        $condition = null;

        $resolver = PaginationType::resolver();
        $resolver->orderByMyIntAsc();

        $page = new Page(2, 1);

        $results = $this->storage->fetchAll($resolver, $page);

        $i = 0;
        foreach($results as $result)
        {
            if ($i === 0)
            {
                $this->assertSame(4, $result->myInt);
            }
            else if ($i === 1)
            {
                $this->assertSame(6, $result->myInt);
            }

            $i++;
        }

        $this->assertSame($i, 2);
    }

    public function testPaginationWithResolvedReference()
    {
        $this->populateDatabase();

        $condition = null;

        $resolver = PaginationType::resolver();
        $resolver->resolveMyReference();
        $resolver->getMyReference()->orderByMyTextAsc();

        $page = new Page(2, 1);

        $results = $this->storage->fetchAll($resolver, $page);

        $i = 0;
        foreach($results as $result)
        {
            if ($i === 0)
            {
                $this->assertSame(6, $result->myInt);
            }
            else if ($i === 1)
            {
                $this->assertSame(4, $result->myInt);
            }

            $i++;
        }

        $this->assertSame($i, 2);
    }

    public function testPaginationWithResolvedScalarCollection()
    {
        $this->populateDatabase();

        $condition = null;

        $resolver = PaginationType::resolver();
        $resolver->resolveCollectionOfInts();
        $resolver->orderByMyIntAsc();
        $resolver->orderCollectionOfIntsAsc();

        $page = new Page(2, 1);

        $results = $this->storage->fetchAll($resolver, $page);

        $i = 0;
        foreach($results as $result)
        {
            if ($i === 0)
            {
                $this->assertSame(4, $result->myInt);
                $this->assertSame([8, 12], $result->collectionOfInts->toArray());
            }
            else if ($i === 1)
            {
                $this->assertSame(6, $result->myInt);
                $this->assertSame([12, 18], $result->collectionOfInts->toArray());
            }

            $i++;
        }

        $this->assertSame($i, 2);
    }

    public function testPaginationWithResolvedReferenceCollection()
    {
        $this->populateDatabase();

        $condition = null;

        $resolver = PaginationType::resolver();
        $resolver->resolveCollectionOfReferences();
        $resolver->orderByMyIntAsc();
        $resolver->getCollectionOfReferences()->orderByMyTextAsc();

        $page = new Page(2, 1);

        $results = $this->storage->fetchAll($resolver, $page);

        $i = 0;
        foreach($results as $result)
        {
            if ($i === 0)
            {
                $this->assertSame(4, $result->myInt);
                $this->assertSame("four", $result->collectionOfReferences->toArray()[0]->myText);
                $this->assertSame("four+", $result->collectionOfReferences->toArray()[1]->myText);
            }
            else if ($i === 1)
            {
                $this->assertSame(6, $result->myInt);
                $this->assertSame("six", $result->collectionOfReferences->toArray()[0]->myText);
                $this->assertSame("six+", $result->collectionOfReferences->toArray()[1]->myText);
            }

            $i++;
        }

        $this->assertSame($i, 2);
    }

    public function testResolverAndPageArgumentsForFetchAll()
    {
        $this->populateDatabase();

        $resolver = PaginationType::resolver();

        $page = new Page(2, 1);

        $results = $this->storage->fetchAll($resolver, $page);

        $i = 0;
        foreach($results as $result)
        {
            $i++;
        }

        $this->assertSame($i, 2);
    }

    public function testConditionAndPageArgumentsForFetchAll()
    {
        $this->populateDatabase();

        $condition = PaginationType::condition();

        $page = new Page(2, 1);

        $results = $this->storage->fetchAll($condition, $page);

        $i = 0;
        foreach($results as $result)
        {
            $i++;
        }

        $this->assertSame($i, 2);
    }

    public function testConditionResolverAndPageArgumentsForFetchAll()
    {
        $this->populateDatabase();

        $condition = PaginationType::condition();
        $resolver = PaginationType::resolver();

        $page = new Page(2, 1);

        $results = $this->storage->fetchAll($condition, $resolver, $page);

        $i = 0;
        foreach($results as $result)
        {
            $i++;
        }

        $this->assertSame($i, 2);
    }

    public function testNextPageWithoutStartAt()
    {
        $page = new Page(5);
        $nextPage = $page->next();

        $this->assertSame(5, $nextPage->getStartAt());
        $this->assertSame(5, $nextPage->getSize());
    }

    public function testNextPageWithStartAt()
    {
        $page = new Page(5, 2);
        $nextPage = $page->next();

        $this->assertSame(7, $nextPage->getStartAt());
        $this->assertSame(5, $nextPage->getSize());
    }

    public function testPageWithCondition()
    {
        $this->populateDatabase();

        $condition = null;

        $resolver = PaginationType::resolver();
        $resolver->orderByMyIntAsc();

        $condition = PaginationType::condition();
        $condition->myInt = new GreaterThan(3);

        $page = new Page(1, 1);

        $results = $this->storage->fetchAll($condition, $resolver, $page);

        $i = 0;
        foreach($results as $result)
        {
            $this->assertSame(6, $result->myInt);

            $i++;
        }

        $this->assertSame($i, 1);
    }

    /**
     * @ticket #175
     */
    public function testPageWithHasACollectionCondition()
    {
        $this->populateDatabase();

        $condition = null;

        $resolver = PaginationType::resolver();
        $resolver->orderByMyIntAsc();

        $condition = PaginationType::condition();
        $condition->collectionOfInts = new HasA(new GreaterThan(12));

        $page = new Page(1, 1);

        $results = $this->storage->fetchAll($condition, $resolver, $page);

        $i = 0;
        foreach($results as $result)
        {
            $this->assertSame(8, $result->myInt);

            $i++;
        }

        $this->assertSame($i, 1);
    }

    /**
     * @ticket #175
     */
    public function testPageWithHasOnlyCollectionCondition()
    {
        $this->populateDatabase();

        $condition = null;

        $resolver = PaginationType::resolver();
        $resolver->orderByMyIntAsc();

        $condition = PaginationType::condition();
        $condition->collectionOfInts = new HasOnly(new GreaterThan(11));

        $page = new Page(1, 1);

        $results = $this->storage->fetchAll($condition, $resolver, $page);

        $i = 0;
        foreach($results as $result)
        {
            $this->assertSame(8, $result->myInt);

            $i++;
        }

        $this->assertSame($i, 1);
    }

    /**
     * @ticket #175
     */
    public function testPageWithHasOnlyCollectionConditionOnReference()
    {
        $this->populateDatabase();

        $condition = null;

        $resolver = PaginationType::resolver();
        $resolver->orderByMyIntAsc();

        $condition = PaginationType::condition();
        $condition->myReference->myInts = new HasOnly(new GreaterThan(11));

        $page = new Page(1, 1);

        $results = $this->storage->fetchAll($condition, $resolver, $page);

        $i = 0;
        foreach($results as $result)
        {
            $this->assertSame(8, $result->myInt);

            $i++;
        }

        $this->assertSame($i, 1);
    }
}

?>
