<?php

/**
 * @runTestsInSeparateProcesses
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

    private function assertSelectObject($result, $from, $where, $order, $by)
    {
        $this->assertSame($from, $result->from);
        $this->assertSame($where, $result->where);
        $this->assertSame($order, $result->order);
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
        file_put_contents(dirname(__FILE__) . '/../testInputFiles/Select.datatype',
                                                                            "datatype Select\n" .
                                                                            "{" .
                                                                            "   int from;\n" .
                                                                            "   float where;\n" .
                                                                            "   text order;\n" .
                                                                            "   datetime by;\n" .
                                                                            '   "Create" group;' . "\n" .
                                                                            "}\n");
        file_put_contents(dirname(__FILE__) . '/../testInputFiles/Create.datatype',
                                                                            "datatype Create\n" .
                                                                            "{" .
                                                                            "   int table;\n" .
                                                                            "   float view;\n" .
                                                                            "   text values;\n" .
                                                                            "   datetime as;\n" .
                                                                            "}\n");

        $rolemodel = new \Good\Rolemodel\Rolemodel();
        $schema = $rolemodel->createSchema(array(dirname(__FILE__) . '/../testInputFiles/Select.datatype',
                                                 dirname(__FILE__) . '/../testInputFiles/Create.datatype'));

        $service = new \Good\Service\Service();
        $service->compile(array(new \Good\Manners\Modifier\Storable()), $schema, dirname(__FILE__) . '/../generated/');

        require dirname(__FILE__) . '/../generated/Select.datatype.php';
        require dirname(__FILE__) . '/../generated/SelectResolver.php';
        require dirname(__FILE__) . '/../generated/Create.datatype.php';
        require dirname(__FILE__) . '/../generated/CreateResolver.php';
    }

    public static function _tearDownAfterClass()
    {
        unlink(dirname(__FILE__) . '/../testInputFiles/Select.datatype');
        unlink(dirname(__FILE__) . '/../testInputFiles/Create.datatype');
        unlink(dirname(__FILE__) . '/../generated/Select.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/Create.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/SelectResolver.php');
        unlink(dirname(__FILE__) . '/../generated/CreateResolver.php');
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

        $this->storage = $this->getNewStorage();
    }

    public function tearDown(): void
    {
        // Just doing this already to make sure the deconstructor will hasve
        // side-effects at an unspecified moment...
        // (at which point the database will probably be in a wrong state for this)
        $this->storage->flush();

        // this should be handled through the GoodManners API once that is implemented
        $this->truncateTable('select');

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
        $this->assertSelectObject($result, 1000, 1.0, "One", new DateTimeImmutable("2001-01-01"));
        $this->assertCreateObject($result->group, 1000, 1.0, "One", new DateTimeImmutable("2001-01-01"));

        $result = $results->getNext();
        $this->assertSelectObject($result, 2000, 2.0, "Two", new DateTimeImmutable("2002-02-02"));
        $this->assertCreateObject($result->group, 2000, 2.0, "Two", new DateTimeImmutable("2002-02-02"));
    }

    public function testDelete()
    {
        $this->populateDatabase();

        $select = new Select();
        $select->from = 1000;

        $condition = new \Good\Manners\Condition\EqualTo($select);

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
        }

        $this->storage->flush();

        $results = $this->getAllSelects();

        $result = $results->getNext();
        $this->assertSelectObject($result, 4000, 4.0, "OneThree", new DateTimeImmutable("2001-01-04"));

        $result = $results->getNext();
        $this->assertSelectObject($result, 5000, 5.0, "TwoThree", new DateTimeImmutable("2002-02-05"));
    }

    public function testModifyAny()
    {
        $this->populateDatabase();

        $select = new Select();
        $select->from = 1000;
        $select->where = 1.0;
        $select->order = "One";
        $select->by = new DateTimeImmutable("2001-01-01");
        $condition = new \Good\Manners\Condition\EqualTo($select);

        $change = new Select();
        $change->from = 9999;
        $change->where = 9.9;
        $change->order = "Nine";
        $change->by = new DateTimeImmutable("2009-09-09");

        $this->storage->modifyAny($condition, $change);

        $results = $this->getAllSelects();

        $result = $results->getNext();
        $this->assertSelectObject($result, 2000, 2.0, "Two", new DateTimeImmutable("2002-02-02"));

        $result = $results->getNext();
        $this->assertSelectObject($result, 9999, 9.9, "Nine", new DateTimeImmutable("2009-09-09"));
    }

    public function testEqualTo()
    {
        $this->populateDatabase();

        $select = new Select();
        $select->from = 1000;
        $select->where = 1.0;
        $select->order = "One";
        $select->by = new DateTimeImmutable("2001-01-01");

        $condition = new \Good\Manners\Condition\EqualTo($select);

        $results = $this->storage->fetchAll($condition);

        $result = $results->getNext();
        $this->assertSelectObject($result, 1000, 1.0, "One", new DateTimeImmutable("2001-01-01"));

        $this->assertSame(null, $results->getNext());
    }

    public function testNotEqualTo()
    {
        $this->populateDatabase();

        $select = new Select();
        $select->from = 1000;
        $select->where = 1.0;
        $select->order = "One";
        $select->by = new DateTimeImmutable("2001-01-01");

        $condition = new \Good\Manners\Condition\NotEqualTo($select);

        $results = $this->storage->fetchAll($condition);

        $result = $results->getNext();
        $this->assertSelectObject($result, 2000, 2.0, "Two", new DateTimeImmutable("2002-02-02"));

        $this->assertSame(null, $results->getNext());
    }

    public function testLessThan()
    {
        $this->populateDatabase();

        $select = new Select();
        $select->from = 2000;
        $select->where = 2.0;
        $select->order = "Two"; // One < Two in lexical ordering
        $select->by = new DateTimeImmutable("2002-02-02");

        $condition = new \Good\Manners\Condition\LessThan($select);

        $results = $this->storage->fetchAll($condition);

        $result = $results->getNext();
        $this->assertSelectObject($result, 1000, 1.0, "One", new DateTimeImmutable("2001-01-01"));

        $this->assertSame(null, $results->getNext());
    }

    public function testLessOrEqual()
    {
        $this->populateDatabase();

        $select = new Select();
        $select->from = 1000;
        $select->where = 1.0;
        $select->order = "One";
        $select->by = new DateTimeImmutable("2001-01-01");

        $condition = new \Good\Manners\Condition\LessOrEqual($select);

        $results = $this->storage->fetchAll($condition);

        $result = $results->getNext();
        $this->assertSelectObject($result, 1000, 1.0, "One", new DateTimeImmutable("2001-01-01"));

        $this->assertSame(null, $results->getNext());
    }

    public function testGreaterThan()
    {
        $this->populateDatabase();

        $select = new Select();
        $select->from = 1000;
        $select->where = 1.0;
        $select->order = "One";
        $select->by = new DateTimeImmutable("2001-01-01");

        $condition = new \Good\Manners\Condition\GreaterThan($select);

        $results = $this->storage->fetchAll($condition);

        $result = $results->getNext();
        $this->assertSelectObject($result, 2000, 2.0, "Two", new DateTimeImmutable("2002-02-02"));

        $this->assertSame(null, $results->getNext());
    }

    public function testGreaterOrEqual()
    {
        $this->populateDatabase();

        $select = new Select();
        $select->from = 1000;
        $select->where = 1.0;
        $select->order = "One";
        $select->by = new DateTimeImmutable("2001-01-01");

        $condition = new \Good\Manners\Condition\GreaterOrEqual($select);

        $resolver = Select::resolver();
        $resolver->orderByFromAsc();

        $results = $this->storage->fetchAll($condition, $resolver);

        $result = $results->getNext();
        $this->assertSelectObject($result, 1000, 1.0, "One", new DateTimeImmutable("2001-01-01"));

        $result = $results->getNext();
        $this->assertSelectObject($result, 2000, 2.0, "Two", new DateTimeImmutable("2002-02-02"));
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
        $this->assertSelectObject($result, 1000, 1.0, "One", new DateTimeImmutable("2001-01-01"));

        $result = $results->getNext();
        $this->assertSelectObject($result, 2000, 2.0, "Two", new DateTimeImmutable("2002-02-02"));
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
        $this->assertSelectObject($result, 2000, 2.0, "Two", new DateTimeImmutable("2002-02-02"));

        $result = $results->getNext();
        $this->assertSelectObject($result, 1000, 1.0, "One", new DateTimeImmutable("2001-01-01"));
    }

    public function testReferenceCondition()
    {
        $this->populateDatabase();

        $select = new Select();
        $select->group = null;

        $condition = new \Good\Manners\Condition\EqualTo($select);

        $results = $this->storage->fetchAll($condition);

        $this->assertSame(null, $results->getNext());
    }

    public function testResolvedReferencePropertiesCondition()
    {
        $this->populateDatabase();

        $select = new Select();
        $select->group = new Create();
        $select->group->table = 1000;
        $select->group->view = 1.0;
        $select->group->values = "One";
        $select->group->as = new DateTimeImmutable("2001-01-01");

        $resolver = Select::resolver();
        $resolver->resolveGroup();

        $condition = new \Good\Manners\Condition\EqualTo($select, $resolver);

        $results = $this->storage->fetchAll($condition);

        $result = $results->getNext();
        $this->assertSelectObject($result, 1000, 1.0, "One", new DateTimeImmutable("2001-01-01"));
    }

    public function testUnresolvedReferencePropertiesCondition()
    {
        $this->populateDatabase();

        $select = new Select();
        $select->group = new Create();
        $select->group->table = 1000;
        $select->group->view = 1.0;
        $select->group->values = "One";
        $select->group->as = new DateTimeImmutable("2001-01-01");

        $condition = new \Good\Manners\Condition\EqualTo($select);

        $results = $this->storage->fetchAll($condition);

        $result = $results->getNext();
        $this->assertSelectObject($result, 1000, 1.0, "One", new DateTimeImmutable("2001-01-01"));
    }

    public function testModifyAnyWithReferencePropertiesInCondition()
    {
        $this->populateDatabase();

        $select = new Select();
        $select->group = new Create();
        $select->group->table = 1000;
        $select->group->view = 1.0;
        $select->group->values = "One";
        $select->group->as = new DateTimeImmutable("2001-01-01");

        $change = new Select();
        $change->from = 0;

        $condition = new \Good\Manners\Condition\EqualTo($select);

        $this->storage->modifyAny($condition, $change);

        $results = $this->getAllSelects();

        $result = $results->getNext();
        $this->assertSame(0, $result->from);

        $result = $results->getNext();
        $this->assertSame(2000, $result->from);
    }
}

?>
