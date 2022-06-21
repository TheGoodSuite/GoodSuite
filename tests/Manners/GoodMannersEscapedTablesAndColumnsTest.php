<?php

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * Uses a type that has a name and a field that are both a SQL keywords
 * to test that both table and column names are escaped properly.
 */
abstract class GoodMannersEscapedTablesAndColumnsTest extends \PHPUnit\Framework\TestCase
{
    abstract public function getNewStorage();
    abstract public function getNewDb();
    // this function should be removed, but is used for clearing the database at the moment
    abstract public function truncateTable($table);

    private function assertNoExceptions()
    {
        $this->assertTrue(true);
    }

    private function assertSelectObject($result, $from, $where, $order, $by, $drop)
    {
        $this->assertSame($from, $result->from);
        $this->assertSame($where, $result->where);
        $this->assertSame($order, $result->order);
        $this->assertSame($drop, $result->drop);
        $this->assertEquals($by, $result->by);
    }

    private function assertCreateObject($result, $table, $view, $values, $as)
    {
        $this->assertSame($table, $result->table);
        $this->assertSame($view, $result->view);
        $this->assertSame($values, $result->values);
        $this->assertEquals($as, $result->as);
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

        $service = new \Good\Service\Service([
            "modifiers" => [new \Good\Manners\Modifier\Storable()],
            "inputDir" => dirname(__FILE__) . '/../testInputFiles/GoodManners/GoodMannersEscapedTablesAndColumnsTest',
            "outputDir" => dirname(__FILE__) . '/../generated/'
        ]);

        $service->load();
    }

    public static function _tearDownAfterClass()
    {
        unlink(dirname(__FILE__) . '/../generated/Select.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/Create.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/SelectResolver.php');
        unlink(dirname(__FILE__) . '/../generated/SelectCondition.php');
        unlink(dirname(__FILE__) . '/../generated/CreateResolver.php');
        unlink(dirname(__FILE__) . '/../generated/CreateCondition.php');
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
        $this->truncateTable('select');
        $this->truncateTable('create');

        $this->storage = $this->getNewStorage();
    }

    public function tearDown(): void
    {
        return;
        // Just doing this already to make sure the deconstructor will hasve
        // side-effects at an unspecified moment...
        // (at which point the database will probably be in a wrong state for this)
        $this->storage->flush();

        // this should be handled through the GoodManners API once that is implemented
        $this->truncateTable('select');
        $this->truncateTable('create');

        $this->_tearDownAfterClass();
    }

    public function populateDatabase()
    {
        $storage = $this->getNewStorage();

        $ins = new Select();
        $ins->from = 1000;
        $ins->where = 1.0;
        $ins->order = "One";
        $ins->by = new DateTimeImmutable("2001-01-01");
        $ins->drop = true;
        $ins->group = new Create();
        $ins->group->table = 1000;
        $ins->group->view = 1.0;
        $ins->group->values = "One";
        $ins->group->as = new DateTimeImmutable("2001-01-01");
        $storage->insert($ins);

        $ins = new Select();
        $ins->from = 2000;
        $ins->where = 2.0;
        $ins->order = "Two";
        $ins->by = new DateTimeImmutable("2002-02-02");
        $ins->drop = false;
        $ins->group = new Create();
        $ins->group->table = 2000;
        $ins->group->view = 2.0;
        $ins->group->values = "Two";
        $ins->group->as = new DateTimeImmutable("2002-02-02");
        $storage->insert($ins);

        $storage->flush();
    }

    public function getAllSelects()
    {
        $storage = $this->getNewStorage();

        $resolver = Select::resolver();
        $resolver->orderByFromAsc();

        return $storage->fetchAll($resolver);
    }

    public function testInsert()
    {
        $select = new Select();
        $select->from = 12;
        $select->where = 3.14159;
        $select->order = "Text";
        $select->by = new DateTimeImmutable("2020-01-01");
        $select->drop = true;

        $this->storage->insert($select);

        // We want any errors happening here, not in the teardown!
        $this->storage->flush();

        $this->assertNoExceptions();
    }

    public function testSelect()
    {
        $this->populateDatabase();

        $resolver = Select::resolver();
        $resolver->resolveGroup();
        $resolver->orderByFromAsc();

        $results = $this->storage->fetchAll($resolver);

        $result = $results->getNext();
        $this->assertSelectObject($result, 1000, 1.0, "One", new DateTimeImmutable("2001-01-01"), true);
        $this->assertCreateObject($result->group, 1000, 1.0, "One", new DateTimeImmutable("2001-01-01"));

        $result = $results->getNext();
        $this->assertSelectObject($result, 2000, 2.0, "Two", new DateTimeImmutable("2002-02-02"), false);
        $this->assertCreateObject($result->group, 2000, 2.0, "Two", new DateTimeImmutable("2002-02-02"));
    }

    public function testDelete()
    {
        $this->populateDatabase();

        $condition = Select::condition();
        $condition->from = 1000;

        foreach ($this->storage->fetchAll($condition) as $select)
        {
            $select->delete();
        }

        $this->storage->flush();

        $results = $this->getAllSelects();

        $firstResult = $results->getNext();
        $this->assertSame(2000, $firstResult->from);

        $this->assertSame(null, $results->getNext());
    }

    public function testSimpleUpdate()
    {
        $this->populateDatabase();

        foreach ($this->storage->fetchAll(Select::resolver()) as $select)
        {
            $select->from += 3000;
            $select->where += 3;
            $select->order .= "Three";
            $select->by = $select->by->modify("+3 days");
            $select->drop = null;
        }

        $this->storage->flush();

        $results = $this->getAllSelects();

        $result = $results->getNext();
        $this->assertSelectObject($result, 4000, 4.0, "OneThree", new DateTimeImmutable("2001-01-04"), null);

        $result = $results->getNext();
        $this->assertSelectObject($result, 5000, 5.0, "TwoThree", new DateTimeImmutable("2002-02-05"), null);
    }

    public function testModifyAny()
    {
        $this->populateDatabase();

        $condition = Select::condition();
        $condition->from = 1000;
        $condition->where = 1.0;
        $condition->order = "One";
        $condition->by = new DateTimeImmutable("2001-01-01");

        $change = new Select();
        $change->from = 9999;
        $change->where = 9.9;
        $change->order = "Nine";
        $change->by = new DateTimeImmutable("2009-09-09");
        $change->drop = null;

        $this->storage->modifyAny($condition, $change);

        $results = $this->getAllSelects();

        $result = $results->getNext();
        $this->assertSelectObject($result, 2000, 2.0, "Two", new DateTimeImmutable("2002-02-02"), false);

        $result = $results->getNext();
        $this->assertSelectObject($result, 9999, 9.9, "Nine", new DateTimeImmutable("2009-09-09"), null);
    }

    public function testEqualTo()
    {
        $this->populateDatabase();

        $condition = Select::condition();
        $condition->from = 1000;
        $condition->where = 1.0;
        $condition->order = "One";
        $condition->by = new DateTimeImmutable("2001-01-01");
        $condition->drop = true;

        $results = $this->storage->fetchAll($condition);

        $result = $results->getNext();
        $this->assertSelectObject($result, 1000, 1.0, "One", new DateTimeImmutable("2001-01-01"), true);

        $this->assertSame(null, $results->getNext());
    }

    public function testNotEqualTo()
    {
        $this->populateDatabase();

        $condition = Select::condition();
        $condition->from = new \Good\Manners\Condition\NotEqualTo(1000);
        $condition->where = new \Good\Manners\Condition\NotEqualTo(1.0);
        $condition->order = new \Good\Manners\Condition\NotEqualTo("One");
        $condition->by = new \Good\Manners\Condition\NotEqualTo(new DateTimeImmutable("2001-01-01"));
        $condition->drop = new \Good\Manners\Condition\NotEqualTo(true);

        $results = $this->storage->fetchAll($condition);

        $result = $results->getNext();
        $this->assertSelectObject($result, 2000, 2.0, "Two", new DateTimeImmutable("2002-02-02"), false);

        $this->assertSame(null, $results->getNext());
    }

    public function testLessThan()
    {
        $this->populateDatabase();

        $condition = Select::condition();
        $condition->from = new \Good\Manners\Condition\LessThan(2000);
        $condition->where = new \Good\Manners\Condition\LessThan(2.0);
        $condition->order = new \Good\Manners\Condition\LessThan("Two"); // One < Two in lexical ordering
        $condition->by = new \Good\Manners\Condition\LessThan(new DateTimeImmutable("2002-02-02"));

        $results = $this->storage->fetchAll($condition);

        $result = $results->getNext();
        $this->assertSelectObject($result, 1000, 1.0, "One", new DateTimeImmutable("2001-01-01"), true);

        $this->assertSame(null, $results->getNext());
    }

    public function testLessOrEqual()
    {
        $this->populateDatabase();

        $condition = Select::condition();
        $condition->from = new \Good\Manners\Condition\LessOrEqual(1000);
        $condition->where = new \Good\Manners\Condition\LessOrEqual(1.0);
        $condition->order = new \Good\Manners\Condition\LessOrEqual("One");
        $condition->by = new \Good\Manners\Condition\LessOrEqual(new DateTimeImmutable("2001-01-01"));

        $results = $this->storage->fetchAll($condition);

        $result = $results->getNext();
        $this->assertSelectObject($result, 1000, 1.0, "One", new DateTimeImmutable("2001-01-01"), true);

        $this->assertSame(null, $results->getNext());
    }

    public function testGreaterThan()
    {
        $this->populateDatabase();

        $condition = Select::condition();
        $condition->from = new \Good\Manners\Condition\GreaterThan(1000);
        $condition->where = new \Good\Manners\Condition\GreaterThan(1.0);
        $condition->order = new \Good\Manners\Condition\GreaterThan("One");
        $condition->by = new \Good\Manners\Condition\GreaterThan(new DateTimeImmutable("2001-01-01"));

        $results = $this->storage->fetchAll($condition);

        $result = $results->getNext();
        $this->assertSelectObject($result, 2000, 2.0, "Two", new DateTimeImmutable("2002-02-02"), false);

        $this->assertSame(null, $results->getNext());
    }

    public function testGreaterOrEqual()
    {
        $this->populateDatabase();

        $condition = Select::condition();
        $condition->from = new \Good\Manners\Condition\GreaterOrEqual(1000);
        $condition->where = new \Good\Manners\Condition\GreaterOrEqual(1.0);
        $condition->order = new \Good\Manners\Condition\GreaterOrEqual("One");
        $condition->by = new \Good\Manners\Condition\GreaterOrEqual(new DateTimeImmutable("2001-01-01"));

        $resolver = Select::resolver();
        $resolver->orderByFromAsc();

        $results = $this->storage->fetchAll($condition, $resolver);

        $result = $results->getNext();
        $this->assertSelectObject($result, 1000, 1.0, "One", new DateTimeImmutable("2001-01-01"), true);

        $result = $results->getNext();
        $this->assertSelectObject($result, 2000, 2.0, "Two", new DateTimeImmutable("2002-02-02"), false);
    }

    public function testOrderByAsc()
    {
        $this->populateDatabase();

        $resolver = Select::resolver();
        $resolver->orderByFromAsc();
        $resolver->orderByWhereAsc();
        $resolver->orderByOrderAsc();
        $resolver->orderByByAsc();

        $results = $this->storage->fetchAll($resolver);

        $result = $results->getNext();
        $this->assertSelectObject($result, 1000, 1.0, "One", new DateTimeImmutable("2001-01-01"), true);

        $result = $results->getNext();
        $this->assertSelectObject($result, 2000, 2.0, "Two", new DateTimeImmutable("2002-02-02"), false);
    }

    public function testOrderByDesc()
    {
        $this->populateDatabase();

        $resolver = Select::resolver();
        $resolver->orderByFromDesc();
        $resolver->orderByWhereDesc();
        $resolver->orderByOrderDesc();
        $resolver->orderByByDesc();

        $results = $this->storage->fetchAll($resolver);

        $result = $results->getNext();
        $this->assertSelectObject($result, 2000, 2.0, "Two", new DateTimeImmutable("2002-02-02"), false);

        $result = $results->getNext();
        $this->assertSelectObject($result, 1000, 1.0, "One", new DateTimeImmutable("2001-01-01"), true);
    }

    public function testReferenceCondition()
    {
        $this->populateDatabase();

        $condition = Select::condition();
        $condition->group = null;

        $results = $this->storage->fetchAll($condition);

        $this->assertSame(null, $results->getNext());
    }

    public function testResolvedReferencePropertiesCondition()
    {
        $this->populateDatabase();

        $condition = Select::condition();
        $condition->group->table = 1000;
        $condition->group->view = 1.0;
        $condition->group->values = "One";
        $condition->group->as = new DateTimeImmutable("2001-01-01");

        $resolver = Select::resolver();
        $resolver->resolveGroup();

        $results = $this->storage->fetchAll($condition, $resolver);

        $result = $results->getNext();
        $this->assertSelectObject($result, 1000, 1.0, "One", new DateTimeImmutable("2001-01-01"), true);
    }

    public function testUnresolvedReferencePropertiesCondition()
    {
        $this->populateDatabase();

        $condition = Select::condition();
        $condition->group->table = 1000;
        $condition->group->view = 1.0;
        $condition->group->values = "One";
        $condition->group->as = new DateTimeImmutable("2001-01-01");

        $results = $this->storage->fetchAll($condition);

        $result = $results->getNext();
        $this->assertSelectObject($result, 1000, 1.0, "One", new DateTimeImmutable("2001-01-01"), true);
    }

    public function testModifyAnyWithReferencePropertiesInCondition()
    {
        $this->populateDatabase();

        $condition = Select::condition();
        $condition->group->table = 1000;
        $condition->group->view = 1.0;
        $condition->group->values = "One";
        $condition->group->as = new DateTimeImmutable("2001-01-01");

        $change = new Select();
        $change->from = 0;

        $this->storage->modifyAny($condition, $change);

        $results = $this->getAllSelects();

        $result = $results->getNext();
        $this->assertSame(0, $result->from);

        $result = $results->getNext();
        $this->assertSame(2000, $result->from);
    }
}

?>
