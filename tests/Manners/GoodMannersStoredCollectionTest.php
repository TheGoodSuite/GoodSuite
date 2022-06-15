<?php

use Good\Manners\Condition\EqualTo;
use Good\Manners\Condition\GreaterThan;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
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

        $service = new \Good\Service\Service([
            "modifiers" => [new \Good\Manners\Modifier\Storable()],
            "inputDir" => dirname(__FILE__) . '/../testInputFiles/',
            "outputDir" => dirname(__FILE__) . '/../generated/'
        ]);

        $service->load();
    }

    public static function _tearDownAfterClass()
    {
        unlink(dirname(__FILE__) . '/../testInputFiles/CollectionType.datatype');
        unlink(dirname(__FILE__) . '/../generated/CollectionType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/CollectionTypeResolver.php');
        unlink(dirname(__FILE__) . '/../generated/CollectionTypeCondition.php');
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

    public function populateDatabase()
    {
        $storage = $this->getNewStorage();

        $myCollectionType = new CollectionType();
        $myCollectionType->someInt = 4;

        $myCollectionType->myInts->add(2);
        $myCollectionType->myInts->add(4);

        $myCollectionType->myFloats->add(2.2);
        $myCollectionType->myFloats->add(4.4);

        $myCollectionType->myTexts->add("abc");
        $myCollectionType->myTexts->add("def");

        $now = new DateTimeImmutable();

        $myCollectionType->myDatetimes->add(new DateTimeImmutable('2001-01-01'));
        $myCollectionType->myDatetimes->add(new DateTimeImmutable('2002-02-02'));

        $reference = new CollectionType();
        $reference->someInt = 1;

        $myCollectionType->myReferences->add($reference);
        $myCollectionType->myReferences->add($myCollectionType);

        $storage->insert($myCollectionType);

        $myCollectionType = new CollectionType();
        $myCollectionType->someInt = 5;

        $myCollectionType->myInts->add(3);
        $myCollectionType->myInts->add(5);

        $myCollectionType->myReferences->add($reference);

        $storage->insert($myCollectionType);

        $storage->flush();
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

    public function testFetchIntCollection()
    {
        $this->populateDatabase();

        $resolver = CollectionType::resolver()->resolveMyInts();
        $condition = CollectionType::condition();
        $condition->someInt = 4;

        $results = $this->storage->fetchAll($condition, $resolver);

        foreach ($results as $result)
        {
            $expected = [2, 4];
            $i = 0;

            $this->assertSame(2, $result->myInts->count());

            foreach ($result->myInts as $myInt)
            {
                $this->assertSame($expected[$i], $myInt);
                $i++;
            }
        }
    }

    public function testFetchFloatCollection()
    {
        $this->populateDatabase();

        $resolver = CollectionType::resolver()->resolveMyFloats();
        $condition = CollectionType::condition();
        $condition->someInt = 4;

        $results = $this->storage->fetchAll($condition, $resolver);

        foreach ($results as $result)
        {
            $expected = [2.2, 4.4];
            $i = 0;

            $this->assertSame(2, $result->myFloats->count());

            foreach ($result->myFloats as $myFloat)
            {
                $this->assertSame($expected[$i], $myFloat);
                $i++;
            }
        }
    }

    public function testFetchDatetimeCollection()
    {
        $this->populateDatabase();

        $resolver = CollectionType::resolver()->resolveMyDatetimes();
        $condition = CollectionType::condition();
        $condition->someInt = 4;

        $results = $this->storage->fetchAll($condition, $resolver);

        foreach ($results as $result)
        {
            $expected = [new DateTimeImmutable('2001-01-01'), new DateTimeImmutable('2002-02-02')];
            $i = 0;

            $this->assertSame(2, $result->myDatetimes->count());

            foreach ($result->myDatetimes as $myDatetime)
            {
                $this->assertEquals($expected[$i]->format(DateTimeImmutable::ATOM), $myDatetime->format(DateTimeImmutable::ATOM));
                $i++;
            }
        }
    }

    public function testFetchTextCollection()
    {
        $this->populateDatabase();

        $resolver = CollectionType::resolver()->resolveMyTexts();
        $condition = CollectionType::condition();
        $condition->someInt = 4;

        $results = $this->storage->fetchAll($condition, $resolver);

        foreach ($results as $result)
        {
            $expected = ["abc", "def"];
            $i = 0;

            $this->assertSame(2, $result->myTexts->count());

            foreach ($result->myTexts as $myText)
            {
                $this->assertEquals($expected[$i], $myText);
                $i++;
            }
        }
    }

    public function testFetchMultipleCollectionsOnOneObject()
    {
        $this->populateDatabase();

        $resolver = CollectionType::resolver();
        $resolver->resolveMyInts();
        $resolver->resolveMyFloats();
        $condition = CollectionType::condition();
        $condition->someInt = 4;

        $results = $this->storage->fetchAll($condition, $resolver);

        foreach ($results as $result)
        {
            $expected = [2.2, 4.4];
            $i = 0;

            $this->assertSame(2, $result->myFloats->count());

            foreach ($result->myFloats as $myFloat)
            {
                $this->assertSame($expected[$i], $myFloat);
                $i++;
            }

            $expected = [2, 4];
            $i = 0;

            $this->assertSame(2, $result->myInts->count());

            foreach ($result->myInts as $myInt)
            {
                $this->assertSame($expected[$i], $myInt);
                $i++;
            }
        }
    }

    public function testFetchReferenceCollection()
    {
        $this->populateDatabase();

        $resolver = CollectionType::resolver()->resolveMyReferences();
        $condition = CollectionType::condition();
        $condition->someInt = 4;

        $results = $this->storage->fetchAll($condition, $resolver);

        foreach ($results as $result)
        {
            $references = $result->myReferences->toArray();

            $this->assertSame(2, count($references));
            $this->assertSame($result->id, $references[0]->id);
            $this->assertSame(4, $references[0]->someInt);
            $this->assertNotSame($result, $references[1]);
            $this->assertNotSame($result->id, $references[1]->id);
            $this->assertSame(1, $references[1]->someInt);
        }
    }

    public function testOrderingCollection()
    {
        $this->populateDatabase();

        $resolver = CollectionType::resolver()->resolveMyInts();
        $resolver->orderMyIntsAsc();

        $condition = CollectionType::condition();
        $condition->someInt = new GreaterThan(2);

        $results = $this->storage->fetchAll($condition, $resolver);

        $count = 0;

        foreach ($results as $result)
        {
            $ints = $result->myInts->toArray();

            $this->assertSame(2, count($ints));
            $this->assertSame($ints[1], $ints[0] + 2);

            $count++;
        }

        $this->assertSame(2, $count);
    }

    public function testOrderingCollectionAndBaseObject()
    {
        $this->populateDatabase();

        $resolver = CollectionType::resolver()->resolveMyInts();
        $resolver->orderMyIntsDesc();
        $resolver->orderBySomeIntAsc();

        $condition = CollectionType::condition();
        $condition->someInt = new GreaterThan(2);

        $results = $this->storage->fetchAll($condition, $resolver);

        $ints = [
            [4, 2],
            [5, 3]
        ];

        $i = 0;

        foreach ($results as $result)
        {
            $j = 0;


            foreach ($result->myInts as $myInt)
            {
                $this->assertSame($ints[$i][$j], $myInt);

                $j++;
            }

            $i++;
        }
    }

    public function testAddItemToResolvedCollection()
    {
        $this->populateDatabase();

        $resolver = CollectionType::resolver()->resolveMyInts();
        $condition = CollectionType::condition();
        $condition->someInt = 4;

        $results = $this->storage->fetchAll($condition, $resolver);

        foreach ($results as $result)
        {
            $result->myInts->add(5);
        }

        $this->storage->flush();

        $resolver->orderMyIntsAsc();
        $results = $this->getNewStorage()->fetchAll($condition, $resolver);

        $result = $results->getNext();

        $this->assertSame(null, $results->getNext());
        $this->assertSame(3, count($result->myInts->toArray()));
        $this->assertEquals(2, $result->myInts->toArray()[0]);
        $this->assertEquals(4, $result->myInts->toArray()[1]);
        $this->assertEquals(5, $result->myInts->toArray()[2]);
    }

    public function testAddItemToUnresolvedCollection()
    {
        $this->populateDatabase();
        $condition = CollectionType::condition();
        $condition->someInt = 4;

        $results = $this->storage->fetchAll($condition);

        foreach ($results as $result)
        {
            $result->myInts->add(5);
        }

        $this->storage->flush();

        $resolver = CollectionType::resolver()->resolveMyInts();
        $resolver->orderMyIntsAsc();
        $results = $this->getNewStorage()->fetchAll($condition, $resolver);

        $result = $results->getNext();

        $this->assertSame(null, $results->getNext());
        $this->assertSame(3, count($result->myInts->toArray()));
        $this->assertEquals(2, $result->myInts->toArray()[0]);
        $this->assertEquals(4, $result->myInts->toArray()[1]);
        $this->assertEquals(5, $result->myInts->toArray()[2]);
    }

    public function testRemoveItemFromResolvedCollection()
    {
        $this->populateDatabase();

        $resolver = CollectionType::resolver()->resolveMyInts();
        $condition = CollectionType::condition();
        $condition->someInt = 4;

        $results = $this->storage->fetchAll($condition, $resolver);

        foreach ($results as $result)
        {
            $result->myInts->remove(2);
        }

        $this->storage->flush();

        $resolver->orderMyIntsAsc();
        $results = $this->getNewStorage()->fetchAll($condition, $resolver);

        $result = $results->getNext();

        $this->assertSame(null, $results->getNext());
        $this->assertSame(1, count($result->myInts->toArray()));
        $this->assertEquals(4, $result->myInts->toArray()[0]);
    }

    public function testRemoveItemFromUnresolvedCollection()
    {
        $this->populateDatabase();
        $condition = CollectionType::condition();
        $condition->someInt = 4;

        $results = $this->storage->fetchAll($condition);

        foreach ($results as $result)
        {
            $result->myInts->remove(2);
        }

        $this->storage->flush();

        $resolver = CollectionType::resolver()->resolveMyInts();
        $resolver->orderMyIntsAsc();
        $results = $this->getNewStorage()->fetchAll($condition, $resolver);

        $result = $results->getNext();

        $this->assertSame(null, $results->getNext());
        $this->assertSame(1, count($result->myInts->toArray()));
        $this->assertEquals(4, $result->myInts->toArray()[0]);
    }

    public function testClearResolvedCollection()
    {
        $this->populateDatabase();

        $resolver = CollectionType::resolver()->resolveMyInts();
        $condition = CollectionType::condition();
        $condition->someInt = 4;

        $results = $this->storage->fetchAll($condition, $resolver);

        foreach ($results as $result)
        {
            $result->myInts->clear();
            $result->myInts->add(123);
        }

        $this->storage->flush();

        $resolver->orderMyIntsAsc();
        $results = $this->getNewStorage()->fetchAll($condition, $resolver);

        $result = $results->getNext();

        $this->assertSame(null, $results->getNext());
        $this->assertSame(1, count($result->myInts->toArray()));
        $this->assertEquals(123, $result->myInts->toArray()[0]);
    }

    public function testClearUnresolvedCollection()
    {
        $this->populateDatabase();
        $condition = CollectionType::condition();
        $condition->someInt = 4;

        $results = $this->storage->fetchAll($condition);

        foreach ($results as $result)
        {
            $result->myInts->clear();
            $result->myInts->add(123);
        }

        $this->storage->flush();

        $resolver = CollectionType::resolver()->resolveMyInts();
        $resolver->orderMyIntsAsc();
        $results = $this->getNewStorage()->fetchAll($condition, $resolver);

        $result = $results->getNext();

        $this->assertSame(null, $results->getNext());
        $this->assertSame(1, count($result->myInts->toArray()));
        $this->assertEquals(123, $result->myInts->toArray()[0]);
    }

    public function testManipulateResolvedReferenceCollection()
    {
        $this->populateDatabase();

        $one = $this->getCollectionObjectBySomeInt(1);
        $five = $this->getCollectionObjectBySomeInt(5);

        $six = new CollectionType();
        $six->someInt = 6;

        $resolver = CollectionType::resolver();
        $resolver->resolveMyReferences();
        $condition = CollectionType::condition();
        $condition->someInt = 4;

        $results = $this->storage->fetchAll($condition, $resolver);

        foreach ($results as $result)
        {
            $result->myReferences->remove($one);
            $result->myReferences->add($five);
            $result->myReferences->add($six);
        }

        $this->storage->flush();

        $resolver->getMyReferences()->orderBySomeIntAsc();
        $results = $this->getNewStorage()->fetchAll($condition, $resolver);

        $result = $results->getNext();

        $this->assertSame(null, $results->getNext());
        $this->assertSame(3, count($result->myReferences->toArray()));
        $ref1 = $result->myReferences->toArray()[0];
        $this->assertEquals(4, $result->myReferences->toArray()[0]->someInt);
        $this->assertEquals(5, $result->myReferences->toArray()[1]->someInt);
        $this->assertEquals(6, $result->myReferences->toArray()[2]->someInt);
    }

    public function testManipulateUnresolvedReferenceCollection()
    {
        $this->populateDatabase();

        $one = $this->getCollectionObjectBySomeInt(1);
        $five = $this->getCollectionObjectBySomeInt(5);

        $six = new CollectionType();
        $six->someInt = 6;

        $condition = CollectionType::condition();
        $condition->someInt = 4;

        $results = $this->storage->fetchAll($condition);

        foreach ($results as $result)
        {
            $result->myReferences->remove($one);
            $result->myReferences->add($five);
            $result->myReferences->add($six);
        }

        $this->storage->flush();

        $resolver = CollectionType::resolver()->resolveMyReferences()->orderBySomeIntAsc();
        $results = $this->getNewStorage()->fetchAll($condition, $resolver);

        $result = $results->getNext();

        $this->assertSame(null, $results->getNext());
        $this->assertSame(3, count($result->myReferences->toArray()));
        $this->assertEquals(4, $result->myReferences->toArray()[0]->someInt);
        $this->assertEquals(5, $result->myReferences->toArray()[1]->someInt);
        $this->assertEquals(6, $result->myReferences->toArray()[2]->someInt);
    }

    private function getCollectionObjectBySomeInt($value)
    {
        $condition = CollectionType::condition();
        $condition->someInt = $value;

        $results = $this->storage->fetchAll($condition);

        return $results->getNext();
    }

    public function testHasAPrimimitiveCollectionCondition()
    {
        $this->populateDatabase();

        $condition = CollectionType::condition();
        $condition->myInts = new \Good\Manners\CollectionCondition\HasA(2);

        $resolver = CollectionType::resolver();
        $resolver->resolveMyInts();

        $results = $this->storage->fetchAll($condition, $resolver);

        $result = $results->getNext();

        $this->assertSame(null, $results->getNext());
        $this->assertSame(4, $result->someInt);
        $this->assertSame(2, count($result->myInts->toArray()));
    }

    public function testHasOnlyPrimitiveCollectionCondition()
    {
        $this->populateDatabase();

        $condition = CollectionType::condition();
        $condition->myInts = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\LessThan(5));

        $resolver = CollectionType::resolver();
        $resolver->resolveMyInts();

        $results = $this->storage->fetchAll($condition, $resolver);

        $result1 = $results->getNext();
        $result2 = $results->getNext();

        $this->assertSame(null, $results->getNext());
        $this->assertSame(4, $result1->someInt);
        $this->assertSame(2, count($result1->myInts->toArray()));
        $this->assertSame(1, $result2->someInt);
        $this->assertSame(0, count($result2->myInts->toArray()));
    }

    public function testHasAReferenceCollectionCondition()
    {
        $this->populateDatabase();

        $subCondition = CollectionType::condition();
        $subCondition->someInt = 4;

        $condition = CollectionType::condition();
        $condition->myReferences = new \Good\Manners\CollectionCondition\HasA($subCondition);

        $resolver = CollectionType::resolver();
        $resolver->resolveMyReferences();

        $results = $this->storage->fetchAll($condition, $resolver);

        $result = $results->getNext();

        $this->assertSame(null, $results->getNext());
        $this->assertSame(4, $result->someInt);
        $this->assertSame(2, count($result->myReferences->toArray()));
    }

    public function testHasOnlyReferenceCollectionCondition()
    {
        $this->populateDatabase();

        $subCondition = CollectionType::condition();
        $subCondition->someInt = 1;

        $condition = CollectionType::condition();
        $condition->myReferences = new \Good\Manners\CollectionCondition\HasOnly($subCondition);

        $resolver = CollectionType::resolver();
        $resolver->resolveMyReferences();

        $results = $this->storage->fetchAll($condition, $resolver);

        $result1 = $results->getNext();
        $result2 = $results->getNext();

        $this->assertSame(null, $results->getNext());
        $this->assertSame(5, $result1->someInt);
        $this->assertSame(1, count($result1->myReferences->toArray()));
        $this->assertSame(1, $result2->someInt);
        $this->assertSame(0, count($result2->myReferences->toArray()));
    }

    public function testHasAHasACollectionCondition()
    {
        $this->populateDatabase();

        $subSubCondition = CollectionType::condition();
        $subSubCondition->someInt = 1;

        $subCondition = CollectionType::condition();
        $subCondition->myReferences = new \Good\Manners\CollectionCondition\HasA($subSubCondition);

        $condition = CollectionType::condition();
        $condition->myReferences = new \Good\Manners\CollectionCondition\HasA($subCondition);

        $results = $this->storage->fetchAll($condition);

        $result = $results->getNext();

        $this->assertSame(null, $results->getNext());
        $this->assertSame(4, $result->someInt);
    }

    public function testHasOnlyHasOnlyCollectionCondition()
    {
        $this->populateDatabase();

        $subSubCondition = CollectionType::condition();
        $subSubCondition->someInt = 4;

        $subCondition = CollectionType::condition();
        $subCondition->myReferences = new \Good\Manners\CollectionCondition\HasOnly($subSubCondition);

        $condition = CollectionType::condition();
        $condition->myReferences = new \Good\Manners\CollectionCondition\HasOnly($subCondition);

        $resolver = CollectionType::resolver();
        $resolver->orderBySomeIntAsc();

        $results = $this->storage->fetchAll($condition, $resolver);

        $result1 = $results->getNext();
        $result2 = $results->getNext();

        $this->assertSame(null, $results->getNext());
        $this->assertSame(1, $result1->someInt);
        $this->assertSame(5, $result2->someInt);
    }

    public function testHasACollectionModifyAny()
    {
        $this->populateDatabase();

        $condition = CollectionType::condition();
        $condition->myInts = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(2));

        $changes = new CollectionType();
        $changes->someInt = 0;

        $this->storage->modifyAny($condition, $changes);

        $condition = CollectionType::condition();
        $condition->someInt = new \Good\Manners\Condition\NotEqualTo(0);

        $results = $this->storage->fetchAll($condition);

        $result = $results->getNext();

        $this->assertSame(null, $results->getNext());
        $this->assertSame(1, $result->someInt);
    }

    public function testHasOnlyCollectionModifyAny()
    {
        $this->populateDatabase();

        $condition = CollectionType::condition();
        $condition->myInts = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(2));

        $changes = new CollectionType();
        $changes->someInt = 0;

        $this->storage->modifyAny($condition, $changes);

        $condition = CollectionType::condition();
        $condition->someInt = new \Good\Manners\Condition\NotEqualTo(0);

        $results = $this->storage->fetchAll($condition);

        $result = $results->getNext();

        $this->assertSame(null, $results->getNext());
        $this->assertSame(4, $result->someInt);
    }

    public function testAndHasOnly()
    {
        $this->populateDatabase();

        $aboveOne = CollectionType::condition();
        $aboveOne->myInts = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(1));

        $aboveThreePointO = CollectionType::condition();
        $aboveThreePointO->myFloats = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(3.0));

        $condition = new \Good\Manners\Condition\AndCondition($aboveOne, $aboveThreePointO);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 2);
    }

    public function testOrHasOnly()
    {
        $this->populateDatabase();

        $aboveOne = CollectionType::condition();
        $aboveOne->myInts = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(1));

        $aboveThreePointO = CollectionType::condition();
        $aboveThreePointO->myFloats = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(3.0));

        $condition = new \Good\Manners\Condition\OrCondition($aboveOne, $aboveThreePointO);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 3);
    }

    public function testImplicitAndHasOnly()
    {
        $this->populateDatabase();

        $condition = CollectionType::condition();
        $condition->myInts = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(1));
        $condition->myFloats = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(3.0));

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 2);
    }

    public function testAndHasA()
    {
        $this->populateDatabase();

        $aboveOne = CollectionType::condition();
        $aboveOne->myInts = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));

        $aboveThreePointO = CollectionType::condition();
        $aboveThreePointO->myFloats = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(3.0));

        $condition = new \Good\Manners\Condition\AndCondition($aboveOne, $aboveThreePointO);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 1);
    }

    public function testOrHasA()
    {
        $this->populateDatabase();

        $aboveOne = CollectionType::condition();
        $aboveOne->myInts = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));

        $aboveThreePointO = CollectionType::condition();
        $aboveThreePointO->myFloats = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(3.0));

        $condition = new \Good\Manners\Condition\OrCondition($aboveOne, $aboveThreePointO);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 2);
    }

    public function testImplicitAndHasA()
    {
        $this->populateDatabase();

        $condition = CollectionType::condition();
        $condition->myInts = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));
        $condition->myFloats = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(3.0));

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 1);
    }

    public function testHasAAndHasOnly()
    {
        $this->populateDatabase();

        $aboveOne = CollectionType::condition();
        $aboveOne->myInts = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));

        $aboveThreePointO = CollectionType::condition();
        $aboveThreePointO->myFloats = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(3.0));

        $condition = new \Good\Manners\Condition\AndCondition($aboveOne, $aboveThreePointO);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 1);
    }

    public function testHasOnlyAndHasA()
    {
        $this->populateDatabase();

        $aboveOne = CollectionType::condition();
        $aboveOne->myInts = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));

        $aboveThreePointO = CollectionType::condition();
        $aboveThreePointO->myFloats = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(3.0));

        $condition = new \Good\Manners\Condition\AndCondition($aboveThreePointO, $aboveOne);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 1);
    }

    public function testHasAOrHasOnly()
    {
        $this->populateDatabase();

        $aboveOne = CollectionType::condition();
        $aboveOne->myInts = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));

        $aboveThreePointO = CollectionType::condition();
        $aboveThreePointO->myFloats = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(3.0));

        $condition = new \Good\Manners\Condition\OrCondition($aboveOne, $aboveThreePointO);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 3);
    }

    public function testHasOnlyOrHasA()
    {
        $this->populateDatabase();

        $aboveOne = CollectionType::condition();
        $aboveOne->myInts = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));

        $aboveThreePointO = CollectionType::condition();
        $aboveThreePointO->myFloats = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(3.0));

        $condition = new \Good\Manners\Condition\OrCondition($aboveThreePointO, $aboveOne);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 3);
    }

    public function testHasAImplicitAndHasOnly()
    {
        $this->populateDatabase();

        $condition = CollectionType::condition();
        $condition->myInts = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));
        $condition->myFloats = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(3.0));

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 1);
    }

    public function testHasOnlyImplicitAndHasA()
    {
        $this->populateDatabase();

        $condition = CollectionType::condition();
        $condition->myFloats = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(3.0));
        $condition->myInts = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 1);
    }

    public function testSinglePropertyOuterAndHasA()
    {
        $this->populateDatabase();

        $aboveOne = CollectionType::condition();
        $aboveOne->myInts = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));

        $belowThree = CollectionType::condition();
        $belowThree->myInts = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\LessThan(3));

        $condition = new \Good\Manners\Condition\AndCondition($aboveOne, $belowThree);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 1);
    }

    public function testSinglePropertyOuterOrHasA()
    {
        $this->populateDatabase();

        $aboveOne = CollectionType::condition();
        $aboveOne->myInts = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));

        $belowThree = CollectionType::condition();
        $belowThree->myInts = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\LessThan(3));

        $condition = new \Good\Manners\Condition\OrCondition($aboveOne, $belowThree);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 2);
    }

    public function testSinglePropertyInnerAndHasA()
    {
        $this->populateDatabase();

        $aboveOne = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));
        $belowThree = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\LessThan(3));

        $condition = CollectionType::condition();
        $condition->myInts = new \Good\Manners\Condition\AndCondition($aboveOne, $belowThree);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 1);
    }

    public function testSinglePropertyInnerOrHasA()
    {
        $this->populateDatabase();

        $aboveOne = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));
        $belowThree = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\LessThan(3));

        $condition = CollectionType::condition();
        $condition->myInts = new \Good\Manners\Condition\OrCondition($aboveOne, $belowThree);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 2);
    }

    public function testSinglePropertyOuterAndHasOnly()
    {
        $this->populateDatabase();

        $aboveOne = CollectionType::condition();
        $aboveOne->myInts = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(1));

        $belowThree = CollectionType::condition();
        $belowThree->myInts = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\LessThan(3));

        $condition = new \Good\Manners\Condition\AndCondition($aboveOne, $belowThree);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 1);
    }

    public function testSinglePropertyOuterOrHasOnly()
    {
        $this->populateDatabase();

        $aboveOne = CollectionType::condition();
        $aboveOne->myInts = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(1));

        $belowThree = CollectionType::condition();
        $belowThree->myInts = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\LessThan(3));

        $condition = new \Good\Manners\Condition\OrCondition($aboveOne, $belowThree);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 3);
    }

    public function testSinglePropertyInnerAndHasOnly()
    {
        $this->populateDatabase();

        $aboveOne = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(1));
        $belowThree = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\LessThan(3));

        $condition = CollectionType::condition();
        $condition->myInts = new \Good\Manners\Condition\AndCondition($aboveOne, $belowThree);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 1);
    }

    public function testSinglePropertyInnerOrHasOnly()
    {
        $this->populateDatabase();

        $aboveOne = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(1));
        $belowThree = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\LessThan(3));

        $condition = CollectionType::condition();
        $condition->myInts = new \Good\Manners\Condition\OrCondition($aboveOne, $belowThree);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 3);
    }

    public function testSinglePropertyHasAOuterAndHasOnly()
    {
        $this->populateDatabase();

        $aboveOne = CollectionType::condition();
        $aboveOne->myInts = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));

        $belowThree = CollectionType::condition();
        $belowThree->myInts = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(2));

        $condition = new \Good\Manners\Condition\AndCondition($aboveOne, $belowThree);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 1);
    }

    public function testSinglePropertyHasOnlyOuterAndHasA()
    {
        $this->populateDatabase();

        $aboveOne = CollectionType::condition();
        $aboveOne->myInts = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));

        $belowThree = CollectionType::condition();
        $belowThree->myInts = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(2));

        $condition = new \Good\Manners\Condition\AndCondition($belowThree, $aboveOne);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 1);
    }

    public function testSinglePropertyHasAOuterOrHasOnly()
    {
        $this->populateDatabase();

        $aboveOne = CollectionType::condition();
        $aboveOne->myInts = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));

        $belowThree = CollectionType::condition();
        $belowThree->myInts = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(2));

        $condition = new \Good\Manners\Condition\OrCondition($aboveOne, $belowThree);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 3);
    }

    public function testSinglePropertyHasOnlyOuterOrHasA()
    {
        $this->populateDatabase();

        $aboveOne = CollectionType::condition();
        $aboveOne->myInts = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));

        $belowThree = CollectionType::condition();
        $belowThree->myInts = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(2));

        $condition = new \Good\Manners\Condition\OrCondition($belowThree, $aboveOne);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 3);
    }

    public function testSinglePropertyHasAInnerAndHasOnly()
    {
        $this->populateDatabase();

        $aboveOne = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));
        $aboveTwo = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(2));

        $condition = CollectionType::condition();
        $condition->myInts = new \Good\Manners\Condition\AndCondition($aboveOne, $aboveTwo);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 1);
    }

    public function testSinglePropertyHasOnlyInnerAndHasA()
    {
        $this->populateDatabase();

        $aboveOne = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));
        $aboveTwo = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(2));

        $condition = CollectionType::condition();
        $condition->myInts = new \Good\Manners\Condition\AndCondition($aboveTwo, $aboveOne);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 1);
    }

    public function testSinglePropertyHasAInnerOrHasOnly()
    {
        $this->populateDatabase();

        $aboveOne = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));
        $aboveTwo = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(2));

        $condition = CollectionType::condition();
        $condition->myInts = new \Good\Manners\Condition\OrCondition($aboveOne, $aboveTwo);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 3);
    }

    public function testSinglePropertyHasOnlyInnerOrHasA()
    {
        $this->populateDatabase();

        $aboveOne = new \Good\Manners\CollectionCondition\HasA(new \Good\Manners\Condition\GreaterThan(1));
        $aboveTwo = new \Good\Manners\CollectionCondition\HasOnly(new \Good\Manners\Condition\GreaterThan(2));

        $condition = CollectionType::condition();
        $condition->myInts = new \Good\Manners\Condition\OrCondition($aboveTwo, $aboveOne);

        $results = $this->storage->fetchAll($condition);

        $count = 0;

        foreach ($results as $result)
        {
            $count++;
        }

        $this->assertSame($count, 3);
    }
}

?>
