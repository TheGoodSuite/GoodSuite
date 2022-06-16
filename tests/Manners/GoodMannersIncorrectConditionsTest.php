<?php

use Good\Manners\Condition\EqualTo;
use Good\Manners\Condition\NotEqualTo;
use Good\Manners\Condition\LessThan;
use Good\Manners\Condition\LessOrEqual;
use Good\Manners\Condition\GreaterThan;
use Good\Manners\Condition\GreaterOrEqual;
use Good\Manners\Condition\AndCondition;
use Good\Manners\Condition\OrCondition;
use Good\Manners\CollectionCondition\HasA;
use Good\Manners\CollectionCondition\HasOnly;

class SomeClass
{
}

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class GoodMannersIncorrectConditionsTest extends \PHPUnit\Framework\TestCase
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
        $service = new \Good\Service\Service([
            "modifiers" => [new \Good\Manners\Modifier\Storable()],
            "inputDir" => dirname(__FILE__) . '/../testInputFiles/GoodMannersIncorrectConditionsTest',
            "outputDir" => dirname(__FILE__) . '/../generated/'
        ]);

        $service->load();
    }

    public static function _tearDownAfterClass()
    {
        unlink(dirname(__FILE__) . '/../generated/IncorrectConditionsType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/IncorrectConditionsTypeResolver.php');
        unlink(dirname(__FILE__) . '/../generated/IncorrectConditionsTypeCondition.php');
        unlink(dirname(__FILE__) . '/../generated/SecondIncorrectType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/SecondIncorrectTypeResolver.php');
        unlink(dirname(__FILE__) . '/../generated/SecondIncorrectTypeCondition.php');
        unlink(dirname(__FILE__) . '/../generated/GeneratedBaseClass.php');
    }

    public function setUp(): void
    {
        $this->_setUpBeforeClass();
    }

    public function tearDown(): void
    {
        $this->_tearDownAfterClass();
    }

    public function testEqualToArray()
    {
        $this->expectException("Exception");

        new EqualTo([]);
    }

    public function testNotEqualToArray()
    {
        $this->expectException("Exception");

        new NotEqualTo([]);
    }

    public function testLessThanArray()
    {
        $this->expectException("Exception");

        new LessThan([]);
    }

    public function testLessOrEqualArray()
    {
        $this->expectException("Exception");

        new LessOrEqual([]);
    }

    public function testGreaterThanArray()
    {
        $this->expectException("Exception");

        new GreaterThan([]);
    }

    public function testGreaterOrEqualArray()
    {
        $this->expectException("Exception");

        new GreaterOrEqual([]);
    }

    public function testEqualToObject()
    {
        $this->expectException("Exception");

        new EqualTo(new SomeClass());
    }

    public function testNotEqualToObject()
    {
        $this->expectException("Exception");

        new NotEqualTo(new SomeClass());
    }

    public function testLessThanObject()
    {
        $this->expectException("Exception");

        new LessThan(new SomeClass());
    }

    public function testLessOrEqualObject()
    {
        $this->expectException("Exception");

        new LessOrEqual(new SomeClass());
    }

    public function testGreaterThanObject()
    {
        $this->expectException("Exception");

        new GreaterThan(new SomeClass());
    }

    public function testGreaterOrEqualObject()
    {
        $this->expectException("Exception");

        new GreaterOrEqual(new SomeClass());
    }

    public function testLessThanStorable()
    {
        $this->expectException("Exception");

        new LessThan(new IncorrectConditionsType());
    }

    public function testLessOrEqualStorable()
    {
        $this->expectException("Exception");

        new LessOrEqual(new IncorrectConditionsType());
    }

    public function testGreaterThanStorable()
    {
        $this->expectException("Exception");

        new GreaterThan(new IncorrectConditionsType());
    }

    public function testGreaterOrEqualStorable()
    {
        $this->expectException("Exception");

        new GreaterOrEqual(new IncorrectConditionsType());
    }

    public function testLessThanNull()
    {
        $this->expectException("Exception");

        new LessThan(null);
    }

    public function testLessOrEqualNull()
    {
        $this->expectException("Exception");

        new LessOrEqual(null);
    }

    public function testGreaterThanNull()
    {
        $this->expectException("Exception");

        new GreaterThan(null);
    }

    public function testGreaterOrEqualNull()
    {
        $this->expectException("Exception");

        new GreaterOrEqual(null);
    }

    public function testObjectAndCondition()
    {
        $this->expectException("Exception");

        new AndCondition(new SomeClass(), IncorrectConditionsType::condition());
    }

    public function testConditionAndObject()
    {
        $this->expectException("Exception");

        new AndCondition(IncorrectConditionsType::condition(), new SomeClass());
    }

    public function testObjectAndObject()
    {
        $this->expectException("Exception");

        new AndCondition(new SomeClass(), new SomeClass());
    }

    public function testObjectOrCondition()
    {
        $this->expectException("Exception");

        new OrCondition(new SomeClass(), IncorrectConditionsType::condition());
    }

    public function testConditionOrObject()
    {
        $this->expectException("Exception");

        new OrCondition(IncorrectConditionsType::condition(), new SomeClass());
    }

    public function testObjectOrObject()
    {
        $this->expectException("Exception");

        new OrCondition(new SomeClass(), new SomeClass());
    }

    public function testArrayAndCondition()
    {
        $this->expectException("Exception");

        new AndCondition([], IncorrectConditionsType::condition());
    }

    public function testConditionAndArray()
    {
        $this->expectException("Exception");

        new AndCondition(IncorrectConditionsType::condition(), []);
    }

    public function testArrayAndArray()
    {
        $this->expectException("Exception");

        new AndCondition([], []);
    }

    public function testArrayOrCondition()
    {
        $this->expectException("Exception");

        new OrCondition([], IncorrectConditionsType::condition());
    }

    public function testConditionOrArray()
    {
        $this->expectException("Exception");

        new OrCondition(IncorrectConditionsType::condition(), []);
    }

    public function testArrayOrArray()
    {
        $this->expectException("Exception");

        new OrCondition([], []);
    }

    public function testNullAndCondition()
    {
        $this->expectException("Exception");

        new AndCondition(null, IncorrectConditionsType::condition());
    }

    public function testConditionAndNull()
    {
        $this->expectException("Exception");

        new AndCondition(IncorrectConditionsType::condition(), null);
    }

    public function testNullAndNull()
    {
        $this->expectException("Exception");

        new AndCondition(null, null);
    }

    public function testNullOrCondition()
    {
        $this->expectException("Exception");

        new OrCondition(null, IncorrectConditionsType::condition());
    }

    public function testConditionOrNull()
    {
        $this->expectException("Exception");

        new OrCondition(IncorrectConditionsType::condition(), null);
    }

    public function testNullOrNull()
    {
        $this->expectException("Exception");

        new OrCondition(null, null);
    }

    public function testObjectAndCollectionCondition()
    {
        $this->expectException("Exception");

        new AndCondition(new SomeClass(), new HasA(IncorrectConditionsType::condition()));
    }

    public function testCollectionConditionAndObject()
    {
        $this->expectException("Exception");

        new AndCondition(new HasA(IncorrectConditionsType::condition()), new SomeClass());
    }

    public function testObjectOrCollectionCondition()
    {
        $this->expectException("Exception");

        new OrCondition(new SomeClass(), new HasA(IncorrectConditionsType::condition()));
    }

    public function testCollectionConditionOrObject()
    {
        $this->expectException("Exception");

        new OrCondition(new HasA(IncorrectConditionsType::condition()), new SomeClass());
    }

    public function testArrayAndCollectionCondition()
    {
        $this->expectException("Exception");

        new AndCondition([], new HasA(IncorrectConditionsType::condition()));
    }

    public function testCollectionConditionAndArray()
    {
        $this->expectException("Exception");

        new AndCondition(new HasA(IncorrectConditionsType::condition()), []);
    }

    public function testArrayOrCollectionCondition()
    {
        $this->expectException("Exception");

        new OrCondition([], new HasA(IncorrectConditionsType::condition()));
    }

    public function testCollectionConditionOrArray()
    {
        $this->expectException("Exception");

        new OrCondition(new HasA(IncorrectConditionsType::condition()), []);
    }

    public function testNullAndCollectionCondition()
    {
        $this->expectException("Exception");

        new AndCondition(null, new HasA(IncorrectConditionsType::condition()));
    }

    public function testCollectionConditionAndNull()
    {
        $this->expectException("Exception");

        new AndCondition(new HasA(IncorrectConditionsType::condition()), null);
    }

    public function testNullOrCollectionCondition()
    {
        $this->expectException("Exception");

        new OrCondition(null, new HasA(IncorrectConditionsType::condition()));
    }

    public function testCollectionConditionOrNull()
    {
        $this->expectException("Exception");

        new OrCondition(new HasA(IncorrectConditionsType::condition()), null);
    }

    public function testConditionOrCollectionCondition()
    {
        $this->expectException("Exception");

        new OrCondition(IncorrectConditionsType::condition(), new HasA(IncorrectConditionsType::condition()));
    }

    public function testCollectionConditionOrCondition()
    {
        $this->expectException("Exception");

        new OrCondition(new HasA(IncorrectConditionsType::condition()), IncorrectConditionsType::condition());
    }

    public function testHasAObject()
    {
        $this->expectException("Exception");

        new HasA(new SomeClass());
    }

    public function testHasAArray()
    {
        $this->expectException("Exception");

        new HasA([]);
    }

    public function testHasACollectionCondition()
    {
        $this->expectException("Exception");

        new HasA(new HasA(IncorrectConditionsType::condition()));
    }

    public function testHasOnlyObject()
    {
        $this->expectException("Exception");

        new HasOnly(new SomeClass());
    }

    public function testHasOnlyArray()
    {
        $this->expectException("Exception");

        new HasOnly([]);
    }

    public function testHasOnlyCollectionCondition()
    {
        $this->expectException("Exception");

        new HasOnly(new HasOnly(IncorrectConditionsType::condition()));
    }

    public function testSetIntConditionToObject()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myInt = new SomeClass();
    }

    public function testSetIntConditionToArray()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myInt = [];
    }

    public function testSetIntConditionToCollectionCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myInt = new HasA(new EqualTo(1));
    }

    public function testSetIntConditionToWrongCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myInt = new EqualTo("a");
    }

    public function testSetFloatConditionToObject()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myFloat = new SomeClass();
    }

    public function testSetFloatConditionToArray()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myFloat = [];
    }

    public function testSetFloatConditionToCollectionCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myFloat = new HasA(new EqualTo(1.1));
    }

    public function testSetFloatConditionToWrongCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myFloat = new EqualTo("a");
    }

    public function testSetTextConditionToObject()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myText = new SomeClass();
    }

    public function testSetTextConditionToArray()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myText = [];
    }

    public function testSetTextConditionToCollectionCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myText = new HasA(new EqualTo("1"));
    }

    public function testSetTextConditionToWrongCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myText = new EqualTo(1);
    }

    public function testSetDateTimeConditionToObject()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myDateTime = new SomeClass();
    }

    public function testSetDateTimeConditionToArray()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myDateTime = [];
    }

    public function testSetDateTimeConditionToCollectionCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myDateTime = new HasA(new EqualTo(new \DateTimeImmutable()));
    }

    public function testSetDateTimeConditionToWrongCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myDateTime = new EqualTo("a");
    }

    public function testSetReferenceConditionToObject()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myReference = new SomeClass();
    }

    public function testSetReferenceConditionToArray()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myReference = [];
    }

    public function testSetReferenceConditionToCollectionCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myReference = new HasA(IncorrectConditionsType::condition());
    }

    public function testSetReferenceConditionToWrongCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myReference = new EqualTo("a");
    }

    public function testSetReferenceConditionToWrongStorableComplexCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myReference = SecondIncorrectType::condition();
    }

    public function testSetReferenceConditionToWrongStorableSimpleCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myReference = new EqualTo(new SecondIncorrectType());
    }

    public function testSetDateTimeCollectionToRegularCondition()
    {
        $this->expectException("TypeError");

        $condition = IncorrectConditionsType::condition();
        $condition->myDateTimeCollection = new EqualTo(new DateTimeImmutable());
    }

    public function testDateTimeCollectionHasAWrongCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myDateTimeCollection = new HasA(new EqualTo(5));
    }

    public function testDateTimeCollectionHasOnlyWrongCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myDateTimeCollection = new HasOnly(new EqualTo(5));
    }

    public function testSetFloatCollectionToRegularCondition()
    {
        $this->expectException("TypeError");

        $condition = IncorrectConditionsType::condition();
        $condition->myFloatCollection = new EqualTo(2.2);
    }

    public function testFloatCollectionHasAWrongCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myFloatCollection = new HasA(new EqualTo("a"));
    }

    public function testFloatCollectionHasOnlyWrongCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myFloatCollection = new HasOnly(new EqualTo("a"));
    }

    public function testSetIntCollectionToRegularCondition()
    {
        $this->expectException("TypeError");

        $condition = IncorrectConditionsType::condition();
        $condition->myIntCollection = new EqualTo(1);
    }

    public function testIntCollectionHasAWrongCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myIntCollection = new HasA(new EqualTo("a"));
    }

    public function testIntCollectionHasOnlyWrongCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myIntCollection = new HasOnly(new EqualTo("a"));
    }

    public function testSetReferenceCollectionToRegularCondition()
    {
        $this->expectException("TypeError");

        $condition = IncorrectConditionsType::condition();
        $condition->myReferenceCollection = IncorrectConditionsType::condition();
    }

    public function testReferenceCollectionHasAWrongCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myReferenceCollection = new HasA(new EqualTo("a"));
    }

    public function testReferenceCollectionHasOnlyWrongCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myReferenceCollection = new HasOnly(new EqualTo("a"));
    }

    public function testReferenceCollectionHasAConditionForWrongReferenceType()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myReferenceCollection = new HasA(SecondIncorrectType::condition());
    }

    public function testReferenceCollectionHasOnlyConditionForWrongReferenceType()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myReferenceCollection = new HasOnly(SecondIncorrectType::condition());
    }

    public function testSetTextCollectionToRegularCondition()
    {
        $this->expectException("TypeError");

        $condition = IncorrectConditionsType::condition();
        $condition->myTextCollection = new EqualTo("abc");
    }

    public function testTextCollectionHasAWrongCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myReferenceCollection = new HasA(new EqualTo(1));
    }

    public function testTextCollectionHasOnlyWrongCondition()
    {
        $this->expectException("Exception");

        $condition = IncorrectConditionsType::condition();
        $condition->myReferenceCollection = new HasOnly(new EqualTo(1));
    }

    public function testEqualToStorableWithoutId()
    {
        $this->expectException("Exception");

        new EqualTo(new IncorrectConditionsType());
    }

    public function testNotEqualToStorableWithoutId()
    {
        $this->expectException("Exception");

        new NotEqualTo(new IncorrectConditionsType());
    }
}

?>
