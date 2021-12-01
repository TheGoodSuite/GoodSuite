<?php

use Good\Manners\Condition\EqualTo;
use Good\Manners\Condition\NotEqualTo;
use Good\Manners\Condition\LessThan;
use Good\Manners\Condition\GreaterThan;
use Good\Manners\Condition\AndCondition;
use Good\Manners\Condition\OrCondition;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
abstract class GoodMannersIdTest extends \PHPUnit\Framework\TestCase
{
    private $storage;

    abstract public function getNewStorage();
    // this function should be removed, but is used for clearing the database at the moment
    abstract public function getNewDb();
    abstract public function truncateTable($table);

    public static function _setUpBeforeClass()
    {
        // Garbage collector causes segmentation fault, so we disable
        // for the duration of the test case
        gc_disable();
        file_put_contents(dirname(__FILE__) . '/../testInputFiles/IdType.datatype',
                                                                            "datatype IdType\n" .
                                                                            "{" .
                                                                            "   text myText;\n" .
                                                                            '   "IdType" reference;' . "\n" .
                                                                            "}\n");

        $rolemodel = new \Good\Rolemodel\Rolemodel();
        $schema = $rolemodel->createSchema(array(dirname(__FILE__) . '/../testInputFiles/IdType.datatype'));

        $service = new \Good\Service\Service();
        $service->compile(array(new \Good\Manners\Modifier\Storable()), $schema, dirname(__FILE__) . '/../generated/');

        require dirname(__FILE__) . '/../generated/IdType.datatype.php';

        require dirname(__FILE__) . '/../generated/IdTypeResolver.php';
        require dirname(__FILE__) . '/../generated/IdTypeCondition.php';
    }

    public static function _tearDownAfterClass()
    {
        unlink(dirname(__FILE__) . '/../testInputFiles/IdType.datatype');
        unlink(dirname(__FILE__) . '/../generated/IdType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/IdTypeResolver.php');
        unlink(dirname(__FILE__) . '/../generated/IdTypeCondition.php');
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
        $this->truncateTable('idtype');

        $storage = $this->getNewStorage();

        $ins = new IdType();
        $ins->myText = 'a';
        $ins->reference = null;
        $storage->insert($ins);
        $reference = $ins;

        $ins = new IdType();
        $ins->myText = 'c';
        $ins->reference = $reference;
        $storage->insert($ins);

        $ins = new IdType();
        $ins->myText = 'b';
        $ins->reference = $reference;
        $storage->insert($ins);

        $storage->flush();

        // new Storage, so communication will have to go through data storage
        $this->storage = $this->getNewStorage();
    }

    public function tearDown(): void
    {
        // Just doing this already to make sure the deconstructor will hasve
        // side-effects at an unspecified moment...
        // (at which point the database will probably be in a wrong state for this)
        $this->storage->flush();

        // this should be handled through the GoodManners API once that is implemented
        $this->truncateTable('idtype');

        $this->_tearDownAfterClass();
    }

    private function idTypeEquals($first, $second)
    {
        return $first->myText == $second->myText &&
               (($first->reference === null && $second->reference === null) ||
                $first->reference !== null && $second->reference !== null &&
                $first->reference->myText == $second->reference->myText);
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
            if ($this->idTypeEquals($hay, $needle))
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

    public function testGetById()
    {
        // first we get a result from the database to find out what irs id is

        // Get the object with text == 'b'
        $condition = IdType::condition();
        $condition->myText = 'b';

        $results = $this->storage->fetchAll($condition, IdType::resolver());

        $idHolder = $results->getNext();

        $resolver = IdType::resolver();
        $resolver->resolveReference();

        $id = IdType::reference($this->storage, $idHolder->getId());

        $result = $id->fetch($resolver);

        $expected = new IdType();
        $expected->myText = 'b';
        $expected->reference = new IdType();
        $expected->reference->myText = 'a';

        $this->assertTrue($this->idTypeEquals($result, $expected));
    }

    public function testDeleteById()
    {
        // first we get a result from the database to find out what irs id is

        // Get the object with text == 'b'
        $condition = IdType::condition();
        $condition->myText = 'b';

        $results = $this->storage->fetchAll($condition, IdType::resolver());

        $idHolder = $results->getNext();

        $id = IdType::reference($this->storage, $idHolder->getId());
        $id->delete();

        $this->storage->flush();

        $resolver = IdType::resolver();
        $resolver->resolveReference();
        $results = $this->storage->fetchAll($resolver);

        $expectedResults = array();

        $expected = new IdType();
        $expected->myText = 'a';
        $expected->reference = null;
        $expectedResults[] = $expected;

        $expected = new IdType();
        $expected->myText = 'c';
        $expected->reference = new IdType();
        $expected->reference->myText = 'a';
        $expectedResults[] = $expected;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    public function testIdInCondition()
    {
        // first we get a result from the database to find out what irs id is

        // Get the object with text == 'a'
        $condition = IdType::condition();
        $condition->myText = 'a';

        $results = $this->storage->fetchAll($condition, IdType::resolver());

        $idHolder = $results->getNext();

        $id = IdType::reference($this->storage, $idHolder->getId());
        $condition = IdType::condition();
        $condition->reference = $id;

        $resolver = IdType::resolver();
        $resolver->resolveReference();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $expected = new IdType();
        $expected->myText = 'b';
        $expected->reference = new IdType();
        $expected->reference->myText = 'a';
        $expectedResults[] = $expected;

        $expected = new IdType();
        $expected->myText = 'c';
        $expected->reference = new IdType();
        $expected->reference->myText = 'a';
        $expectedResults[] = $expected;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    public function testIdInModification()
    {
        $condition = IdType::condition();
        $condition->myText = 'a';

        $results = $this->storage->fetchAll($condition, IdType::resolver());

        $a = $results->getNext();

        $condition = IdType::condition();
        $condition->myText = 'b';

        $results = $this->storage->fetchAll($condition, IdType::resolver());

        $b = $results->getNext();

        $id = IdType::reference($this->storage, $b->getId());

        $a->reference = $id;

        $this->storage->flush();

        $resolver = IdType::resolver();
        $resolver->resolveReference();
        $results = $this->storage->fetchAll($resolver);

        $expectedResults = array();

        $expected = new IdType();
        $expected->myText = 'a';
        $expected->reference = new IdType();
        $expected->reference->myText = 'b';
        $expectedResults[] = $expected;

        $expected = new IdType();
        $expected->myText = 'b';
        $expected->reference = new IdType();
        $expected->reference->myText = 'a';
        $expectedResults[] = $expected;

        $expected = new IdType();
        $expected->myText = 'c';
        $expected->reference = new IdType();
        $expected->reference->myText = 'a';
        $expectedResults[] = $expected;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    public function testIdInModifyAny()
    {
        $condition = IdType::condition();
        $condition->myText = 'b';

        $results = $this->storage->fetchAll($condition, IdType::resolver());

        $idHolder = $results->getNext();

        $id = IdType::reference($this->storage, $idHolder->getId());

        $a = IdType::condition();
        $a->myText = 'a';
        $b = IdType::condition();
        $b->myText = 'b';

        $aOrB = new \Good\Manners\Condition\OrCondition($a, $b);

        $referenceIsId = new IdType();
        $referenceIsId->reference = $id;

        $this->storage->modifyAny($aOrB, $referenceIsId);

        $resolver = IdType::resolver();
        $resolver->resolveReference();
        $results = $this->storage->fetchAll($resolver);

        $expectedResults = array();

        $expected = new IdType();
        $expected->myText = 'a';
        $expected->reference = new IdType();
        $expected->reference->myText = 'b';
        $expectedResults[] = $expected;

        $expected = new IdType();
        $expected->myText = 'b';
        $expected->reference = new IdType();
        $expected->reference->myText = 'b';
        $expectedResults[] = $expected;

        $expected = new IdType();
        $expected->myText = 'c';
        $expected->reference = new IdType();
        $expected->reference->myText = 'a';
        $expectedResults[] = $expected;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    public function testIdInEqualTo()
    {
        // first we get a result from the database to find out what irs id is

        // Get the object with text == 'a'
        $condition = IdType::condition();
        $condition->myText = 'a';

        $results = $this->storage->fetchAll($condition, IdType::resolver());

        $idHolder = $results->getNext();

        $id = IdType::reference($this->storage, $idHolder->getId());
        $condition = new EqualTo($id);

        $resolver = IdType::resolver();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $expected = new IdType();
        $expected->myText = 'a';
        $expectedResults[] = $expected;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    public function testIdInNotEqualTo()
    {
        // first we get a result from the database to find out what irs id is

        // Get the object with text == 'a'
        $condition = IdType::condition();
        $condition->myText = 'a';

        $results = $this->storage->fetchAll($condition, IdType::resolver());

        $idHolder = $results->getNext();

        $id = IdType::reference($this->storage, $idHolder->getId());
        $condition = new NotEqualTo($id);

        $resolver = IdType::resolver();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $expected = new IdType();
        $expected->myText = 'b';
        $expectedResults[] = $expected;

        $expected = new IdType();
        $expected->myText = 'c';
        $expectedResults[] = $expected;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    public function testIdInAndNotEqualTo()
    {
        // first we get a result from the database to find out what irs id is

        // Get the object with text == 'a'
        $condition = IdType::condition();
        $condition->myText = 'a';

        $results = $this->storage->fetchAll($condition, IdType::resolver());

        $idHolder = $results->getNext();

        $id = IdType::reference($this->storage, $idHolder->getId());
        $belowC = IdType::condition();
        $belowC->myText = new LessThan('c');

        $condition = new AndCondition($belowC, new NotEqualTo($id));

        $resolver = IdType::resolver();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $expected = new IdType();
        $expected->myText = 'b';
        $expectedResults[] = $expected;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }

    public function testIdInOrEqualTo()
    {
        // first we get a result from the database to find out what irs id is

        // Get the object with text == 'a'
        $condition = IdType::condition();
        $condition->myText = 'a';

        $results = $this->storage->fetchAll($condition, IdType::resolver());

        $idHolder = $results->getNext();

        $id = IdType::reference($this->storage, $idHolder->getId());
        $aboveB = IdType::condition();
        $aboveB->myText = new GreaterThan('b');

        $condition = new OrCondition($aboveB, new EqualTo($id));

        $resolver = IdType::resolver();
        $results = $this->storage->fetchAll($condition, $resolver);

        $expectedResults = array();

        $expected = new IdType();
        $expected->myText = 'a';
        $expectedResults[] = $expected;

        $expected = new IdType();
        $expected->myText = 'c';
        $expectedResults[] = $expected;

        foreach ($results as $type)
        {
            $pos = $this->assertContainsAndReturnIndex_specific($type, $expectedResults);

            array_splice($expectedResults, $pos, 1);
        }

        $this->assertSame(array(), $expectedResults);
    }
}

?>
