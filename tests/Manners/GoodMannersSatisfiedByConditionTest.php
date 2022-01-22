<?php

use Good\Manners\Condition\LessThan;
use Good\Manners\Condition\LessOrEqual;
use Good\Manners\Condition\GreaterThan;
use Good\Manners\Condition\GreaterOrEqual;
use Good\Manners\Condition\EqualTo;
use Good\Manners\Condition\NotEqualTo;
use Good\Manners\Condition\AndCondition;
use Good\Manners\Condition\OrCondition;
use Good\Manners\CollectionCondition\HasA;
use Good\Manners\CollectionCondition\HasOnly;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class GoodMannersSatisfiedByConditionTest extends \PHPUnit\Framework\TestCase
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
        file_put_contents(dirname(__FILE__) . '/../testInputFiles/SatisfiedByConditionType.datatype',
                                                                            "datatype SatisfiedByConditionType\n" .
                                                                            "{" .
                                                                            "   int myInt;\n" .
                                                                            "   text myText;\n" .
                                                                            "   int[] myInts;\n" .
                                                                            "}\n");

        $rolemodel = new \Good\Rolemodel\Rolemodel();
        $schema = $rolemodel->createSchema(array(dirname(__FILE__) . '/../testInputFiles/SatisfiedByConditionType.datatype'));

        $service = new \Good\Service\Service();
        $service->compile(array(new \Good\Manners\Modifier\Storable()), $schema, dirname(__FILE__) . '/../generated/');

        require dirname(__FILE__) . '/../generated/SatisfiedByConditionType.datatype.php';
        require dirname(__FILE__) . '/../generated/SatisfiedByConditionTypeResolver.php';
        require dirname(__FILE__) . '/../generated/SatisfiedByConditionTypeCondition.php';
    }

    public static function _tearDownAfterClass()
    {
        unlink(dirname(__FILE__) . '/../testInputFiles/SatisfiedByConditionType.datatype');
        unlink(dirname(__FILE__) . '/../generated/SatisfiedByConditionType.datatype.php');
        unlink(dirname(__FILE__) . '/../generated/SatisfiedByConditionTypeResolver.php');
        unlink(dirname(__FILE__) . '/../generated/SatisfiedByConditionTypeCondition.php');
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

    public function testIntLessThanSucceedsCondition()
    {
        $condition = new LessThan(5);

        $result = $condition->isSatisfiedBy(4);

        $this->assertEquals($result, true);
    }

    public function testIntLessThanSameValueCondition()
    {
        $condition = new LessThan(5);

        $result = $condition->isSatisfiedBy(5);

        $this->assertEquals($result, false);
    }

    public function testIntLessThanFailsCondition()
    {
        $condition = new LessThan(5);

        $result = $condition->isSatisfiedBy(6);

        $this->assertEquals($result, false);
    }

    public function testIntLessThanNullCondition()
    {
        $condition = new LessThan(5);

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, false);
    }

    public function testIntLessOrEqualSucceedsCondition()
    {
        $condition = new LessOrEqual(5);

        $result = $condition->isSatisfiedBy(4);

        $this->assertEquals($result, true);
    }

    public function testIntLessOrEqualSameValueCondition()
    {
        $condition = new LessOrEqual(5);

        $result = $condition->isSatisfiedBy(5);

        $this->assertEquals($result, true);
    }

    public function testIntLessOrEqualFailsCondition()
    {
        $condition = new LessOrEqual(5);

        $result = $condition->isSatisfiedBy(6);

        $this->assertEquals($result, false);
    }

    public function testIntLessOrEqualNullCondition()
    {
        $condition = new LessOrEqual(5);

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, false);
    }

    public function testIntGreaterThanSucceedsCondition()
    {
        $condition = new GreaterThan(5);

        $result = $condition->isSatisfiedBy(6);

        $this->assertEquals($result, true);
    }

    public function testIntGreaterThanSameValueCondition()
    {
        $condition = new GreaterThan(5);

        $result = $condition->isSatisfiedBy(5);

        $this->assertEquals($result, false);
    }

    public function testIntGreaterThanFailsCondition()
    {
        $condition = new GreaterThan(5);

        $result = $condition->isSatisfiedBy(4);

        $this->assertEquals($result, false);
    }

    public function testIntGreaterThanNullCondition()
    {
        $condition = new GreaterThan(-5);

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, false);
    }

    public function testIntGreaterOrEqualSucceedsCondition()
    {
        $condition = new GreaterOrEqual(5);

        $result = $condition->isSatisfiedBy(6);

        $this->assertEquals($result, true);
    }

    public function testIntGreaterOrEqualSameValueCondition()
    {
        $condition = new GreaterOrEqual(5);

        $result = $condition->isSatisfiedBy(5);

        $this->assertEquals($result, true);
    }

    public function testIntGreaterOrEqualFailsCondition()
    {
        $condition = new GreaterOrEqual(5);

        $result = $condition->isSatisfiedBy(4);

        $this->assertEquals($result, false);
    }

    public function testIntGreaterOrEqualNullCondition()
    {
        $condition = new GreaterOrEqual(-5);

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, false);
    }

    public function testIntEqualToSucceedsCondition()
    {
        $condition = new EqualTo(5);

        $result = $condition->isSatisfiedBy(5);

        $this->assertEquals($result, true);
    }

    public function testIntEqualToFailsCondition()
    {
        $condition = new EqualTo(5);

        $result = $condition->isSatisfiedBy(4);

        $this->assertEquals($result, false);
    }

    public function testIntEqualToNullCondition()
    {
        $condition = new EqualTo(null);

        $result = $condition->isSatisfiedBy(0);

        $this->assertEquals($result, false);
    }

    public function testIntNullEqualToCondition()
    {
        $condition = new EqualTo(0);

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, false);
    }

    public function testIntNotEqualToSucceedsCondition()
    {
        $condition = new NotEqualTo(5);

        $result = $condition->isSatisfiedBy(4);

        $this->assertEquals($result, true);
    }

    public function testIntNotEqualToFailsCondition()
    {
        $condition = new NotEqualTo(5);

        $result = $condition->isSatisfiedBy(5);

        $this->assertEquals($result, false);
    }

    public function testIntNotEqualToNullCondition()
    {
        $condition = new NotEqualTo(null);

        $result = $condition->isSatisfiedBy(0);

        $this->assertEquals($result, true);
    }

    public function testIntNullNotEqualToFailsCondition()
    {
        $condition = new NotEqualTo(0);

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, true);
    }

    public function testFloatLessThanSucceedsCondition()
    {
        $condition = new LessThan(5.5);

        $result = $condition->isSatisfiedBy(4.0);

        $this->assertEquals($result, true);
    }

    public function testFloatLessThanSameValueCondition()
    {
        $condition = new LessThan(5.5);

        $result = $condition->isSatisfiedBy(5.5);

        $this->assertEquals($result, false);
    }

    public function testFloatLessThanFailsCondition()
    {
        $condition = new LessThan(5.5);

        $result = $condition->isSatisfiedBy(6.0);

        $this->assertEquals($result, false);
    }

    public function testFloatLessThanNullCondition()
    {
        $condition = new LessThan(5.5);

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, false);
    }

    public function testFloatLessOrEqualSucceedsCondition()
    {
        $condition = new LessOrEqual(5.5);

        $result = $condition->isSatisfiedBy(4.0);

        $this->assertEquals($result, true);
    }

    public function testFloatLessOrEqualSameValueCondition()
    {
        $condition = new LessOrEqual(5.5);

        $result = $condition->isSatisfiedBy(5.5);

        $this->assertEquals($result, true);
    }

    public function testFloatLessOrEqualFailsCondition()
    {
        $condition = new LessOrEqual(5.5);

        $result = $condition->isSatisfiedBy(6.0);

        $this->assertEquals($result, false);
    }

    public function testFloatLessOrEqualNullCondition()
    {
        $condition = new LessOrEqual(5.5);

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, false);
    }

    public function testFloatGreaterThanSucceedsCondition()
    {
        $condition = new GreaterThan(5.5);

        $result = $condition->isSatisfiedBy(6.0);

        $this->assertEquals($result, true);
    }

    public function testFloatGreaterThanSameValueCondition()
    {
        $condition = new GreaterThan(5.5);

        $result = $condition->isSatisfiedBy(5.5);

        $this->assertEquals($result, false);
    }

    public function testFloatGreaterThanFailsCondition()
    {
        $condition = new GreaterThan(5.5);

        $result = $condition->isSatisfiedBy(4.0);

        $this->assertEquals($result, false);
    }

    public function testFloatGreaterThanNullCondition()
    {
        $condition = new GreaterThan(-5.5);

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, false);
    }

    public function testFloatGreaterOrEqualSucceedsCondition()
    {
        $condition = new GreaterOrEqual(5.5);

        $result = $condition->isSatisfiedBy(6.0);

        $this->assertEquals($result, true);
    }

    public function testFloatGreaterOrEqualSameValueCondition()
    {
        $condition = new GreaterOrEqual(5.5);

        $result = $condition->isSatisfiedBy(5.5);

        $this->assertEquals($result, true);
    }

    public function testFloatGreaterOrEqualFailsCondition()
    {
        $condition = new GreaterOrEqual(5.5);

        $result = $condition->isSatisfiedBy(4.0);

        $this->assertEquals($result, false);
    }

    public function testFloatGreaterOrEqualNullCondition()
    {
        $condition = new GreaterOrEqual(-5.5);

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, false);
    }

    public function testFloatEqualToSucceedsCondition()
    {
        $condition = new EqualTo(5.5);

        $result = $condition->isSatisfiedBy(5.5);

        $this->assertEquals($result, true);
    }

    public function testFloatEqualToFailsCondition()
    {
        $condition = new EqualTo(5.5);

        $result = $condition->isSatisfiedBy(4.0);

        $this->assertEquals($result, false);
    }

    public function testFloatEqualToNullCondition()
    {
        $condition = new EqualTo(null);

        $result = $condition->isSatisfiedBy(0.0);

        $this->assertEquals($result, false);
    }

    public function testFloatNullEqualToCondition()
    {
        $condition = new EqualTo(0.0);

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, false);
    }

    public function testFloatNotEqualToSucceedsCondition()
    {
        $condition = new NotEqualTo(5.5);

        $result = $condition->isSatisfiedBy(4.0);

        $this->assertEquals($result, true);
    }

    public function testFloatNotEqualToFailsCondition()
    {
        $condition = new NotEqualTo(5.5);

        $result = $condition->isSatisfiedBy(5.5);

        $this->assertEquals($result, false);
    }

    public function testFloatNotEqualToNullCondition()
    {
        $condition = new NotEqualTo(null);

        $result = $condition->isSatisfiedBy(0.0);

        $this->assertEquals($result, true);
    }

    public function testFloatNullNotEqualToCondition()
    {
        $condition = new NotEqualTo(0.0);

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, true);
    }

    public function testStringLessThanSucceedsCondition()
    {
        $condition = new LessThan("abc");

        $result = $condition->isSatisfiedBy("aaa");

        $this->assertEquals($result, true);
    }

    public function testStringLessThanSameValueCondition()
    {
        $condition = new LessThan("abc");

        $result = $condition->isSatisfiedBy("abc");

        $this->assertEquals($result, false);
    }

    public function testStringLessThanFailsCondition()
    {
        $condition = new LessThan("abc");

        $result = $condition->isSatisfiedBy("abcd");

        $this->assertEquals($result, false);
    }

    public function testStringLessThanNullCondition()
    {
        $condition = new LessThan("abc");

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, false);
    }

    public function testStringLessOrEqualSucceedsCondition()
    {
        $condition = new LessOrEqual("abc");

        $result = $condition->isSatisfiedBy("aaa");

        $this->assertEquals($result, true);
    }

    public function testStringLessOrEqualSameValueCondition()
    {
        $condition = new LessOrEqual("abc");

        $result = $condition->isSatisfiedBy("abc");

        $this->assertEquals($result, true);
    }

    public function testStringLessOrEqualFailsCondition()
    {
        $condition = new LessOrEqual("abc");

        $result = $condition->isSatisfiedBy("abcd");

        $this->assertEquals($result, false);
    }

    public function testStringLessOrEqualNullCondition()
    {
        $condition = new LessOrEqual("abc");

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, false);
    }

    public function testStringGreaterThanSucceedsCondition()
    {
        $condition = new GreaterThan("abc");

        $result = $condition->isSatisfiedBy("abcd");

        $this->assertEquals($result, true);
    }

    public function testStringGreaterThanSameValueCondition()
    {
        $condition = new GreaterThan("abc");

        $result = $condition->isSatisfiedBy("abc");

        $this->assertEquals($result, false);
    }

    public function testStringGreaterThanFailsCondition()
    {
        $condition = new GreaterThan("abc");

        $result = $condition->isSatisfiedBy("aaa");

        $this->assertEquals($result, false);
    }

    public function testStringGreaterThanNullCondition()
    {
        $condition = new GreaterThan("");

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, false);
    }

    public function testStringGreaterOrEqualSucceedsCondition()
    {
        $condition = new GreaterOrEqual("abc");

        $result = $condition->isSatisfiedBy("abcd");

        $this->assertEquals($result, true);
    }

    public function testStringGreaterOrEqualSameValueCondition()
    {
        $condition = new GreaterOrEqual("abc");

        $result = $condition->isSatisfiedBy("abc");

        $this->assertEquals($result, true);
    }

    public function testStringGreaterOrEqualFailsCondition()
    {
        $condition = new GreaterOrEqual("abc");

        $result = $condition->isSatisfiedBy("aaa");

        $this->assertEquals($result, false);
    }

    public function testStringGreaterOrEqualNullCondition()
    {
        $condition = new GreaterOrEqual("");

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, false);
    }

    public function testStringEqualToSucceedsCondition()
    {
        $condition = new EqualTo("abc");

        $result = $condition->isSatisfiedBy("abc");

        $this->assertEquals($result, true);
    }

    public function testStringEqualToFailsCondition()
    {
        $condition = new EqualTo("abc");

        $result = $condition->isSatisfiedBy("aaa");

        $this->assertEquals($result, false);
    }

    public function testStringEqualToNullCondition()
    {
        $condition = new EqualTo("");

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, false);
    }

    public function testStringNullEqualToCondition()
    {
        $condition = new EqualTo(null);

        $result = $condition->isSatisfiedBy("");

        $this->assertEquals($result, false);
    }

    public function testStringNotEqualToSucceedsCondition()
    {
        $condition = new NotEqualTo("abc");

        $result = $condition->isSatisfiedBy("aaa");

        $this->assertEquals($result, true);
    }

    public function testStringNotEqualToFailsCondition()
    {
        $condition = new NotEqualTo("abc");

        $result = $condition->isSatisfiedBy("abc");

        $this->assertEquals($result, false);
    }

    public function testStringNotEqualToNullCondition()
    {
        $condition = new NotEqualTo(null);

        $result = $condition->isSatisfiedBy("");

        $this->assertEquals($result, true);
    }

    public function testStringNullNotEqualToCondition()
    {
        $condition = new NotEqualTo("");

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, true);
    }

    public function testDateTimeLessThanSucceedsCondition()
    {
        $condition = new LessThan(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(new DateTimeImmutable("2000-01-01T12:00"));

        $this->assertEquals($result, true);
    }

    public function testDateTimeLessThanSameValueCondition()
    {
        $condition = new LessThan(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(new DateTimeImmutable("2022-01-01T12:00"));

        $this->assertEquals($result, false);
    }

    public function testDateTimeLessThanFailsCondition()
    {
        $condition = new LessThan(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(new DateTimeImmutable("2025-01-01T12:00"));

        $this->assertEquals($result, false);
    }

    public function testDateTimeLessThanNullCondition()
    {
        $condition = new LessThan(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, false);
    }

    public function testDateTimeLessOrEqualSameValueCondition()
    {
        $condition = new LessOrEqual(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(new DateTimeImmutable("2022-01-01T12:00"));

        $this->assertEquals($result, true);
    }

    public function testDateTimeLessOrEqualSucceedsCondition()
    {
        $condition = new LessOrEqual(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(new DateTimeImmutable("2000-01-01T12:00"));

        $this->assertEquals($result, true);
    }

    public function testDateTimeLessOrEqualFailsCondition()
    {
        $condition = new LessOrEqual(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(new DateTimeImmutable("2025-01-01T12:00"));

        $this->assertEquals($result, false);
    }

    public function testDateTimeLessOrEqualNullCondition()
    {
        $condition = new LessOrEqual(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, false);
    }

    public function testDateTimeGreaterThanSucceedsCondition()
    {
        $condition = new GreaterThan(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(new DateTimeImmutable("2025-01-01T12:00"));

        $this->assertEquals($result, true);
    }

    public function testDateTimeGreaterThanSameValueCondition()
    {
        $condition = new GreaterThan(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(new DateTimeImmutable("2022-01-01T12:00"));

        $this->assertEquals($result, false);
    }

    public function testDateTimeGreaterThanFailsCondition()
    {
        $condition = new GreaterThan(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(new DateTimeImmutable("2000-01-01T12:00"));

        $this->assertEquals($result, false);
    }

    public function testDateTimeGreaterThanNullCondition()
    {
        $condition = new GreaterThan(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, false);
    }

    public function testDateTimeGreaterOrEqualSucceedsCondition()
    {
        $condition = new GreaterOrEqual(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(new DateTimeImmutable("2025-01-01T12:00"));

        $this->assertEquals($result, true);
    }

    public function testDateTimeGreaterOrEqualSameValueCondition()
    {
        $condition = new GreaterOrEqual(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(new DateTimeImmutable("2022-01-01T12:00"));

        $this->assertEquals($result, true);
    }

    public function testDateTimeGreaterOrEqualFailsCondition()
    {
        $condition = new GreaterOrEqual(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(new DateTimeImmutable("2000-01-01T12:00"));

        $this->assertEquals($result, false);
    }

    public function testDateTimeGreaterOrEqualNullCondition()
    {
        $condition = new GreaterOrEqual(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, false);
    }

    public function testDateTimeEqualToSucceedsCondition()
    {
        $condition = new EqualTo(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(new DateTimeImmutable("2022-01-01T12:00"));

        $this->assertEquals($result, true);
    }

    public function testDateTimeEqualToFailsCondition()
    {
        $condition = new EqualTo(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(new DateTimeImmutable("2000-01-01T12:00"));

        $this->assertEquals($result, false);
    }

    public function testDateTimeEqualToNullCondition()
    {
        $condition = new EqualTo(null);

        $result = $condition->isSatisfiedBy(new DateTimeImmutable("2000-01-01T12:00"));

        $this->assertEquals($result, false);
    }

    public function testDateTimeNullEqualToCondition()
    {
        $condition = new EqualTo(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, false);
    }

    public function testDateTimeNotEqualToSucceedsCondition()
    {
        $condition = new NotEqualTo(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(new DateTimeImmutable("2000-01-01T12:00"));

        $this->assertEquals($result, true);
    }

    public function testDateTimeNotEqualToFailsCondition()
    {
        $condition = new NotEqualTo(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(new DateTimeImmutable("2022-01-01T12:00"));

        $this->assertEquals($result, false);
    }

    public function testDateTimeNotEqualToNullCondition()
    {
        $condition = new NotEqualTo(null);

        $result = $condition->isSatisfiedBy(new DateTimeImmutable("2022-01-01T12:00"));

        $this->assertEquals($result, true);
    }

    public function testDateTimeNullNotEqualToCondition()
    {
        $condition = new NotEqualTo(new DateTimeImmutable("2022-01-01T12:00"));

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, true);
    }

    public function testNullEqualToNullCondition()
    {
        $condition = new EqualTo(null);

        $result = $condition->isSatisfiedBy(null);

        $this->assertEquals($result, true);
    }

    public function testAndConditionBothSucceed()
    {
        $less = new LessThan(5);
        $greater = new GreaterThan(3);
        $condition = new AndCondition($less, $greater);

        $result = $condition->isSatisfiedBy(4);

        $this->assertEquals($result, true);
    }

    public function testAndConditionFirstFails()
    {
        $less = new LessThan(5);
        $greater = new GreaterThan(3);
        $condition = new AndCondition($less, $greater);

        $result = $condition->isSatisfiedBy(6);

        $this->assertEquals($result, false);
    }

    public function testAndConditionSecondFails()
    {
        $less = new LessThan(5);
        $greater = new GreaterThan(3);
        $condition = new AndCondition($less, $greater);

        $result = $condition->isSatisfiedBy(2);

        $this->assertEquals($result, false);
    }

    public function testAndConditionBothFail()
    {
        $less = new LessThan(5);
        $greater = new GreaterThan(7);
        $condition = new AndCondition($less, $greater);

        $result = $condition->isSatisfiedBy(6);

        $this->assertEquals($result, false);
    }

    public function testOrConditionBothSucceed()
    {
        $less = new LessThan(5);
        $greater = new GreaterThan(3);
        $condition = new OrCondition($less, $greater);

        $result = $condition->isSatisfiedBy(4);

        $this->assertEquals($result, true);
    }

    public function testOrConditionFirstFails()
    {
        $less = new LessThan(5);
        $greater = new GreaterThan(3);
        $condition = new OrCondition($less, $greater);

        $result = $condition->isSatisfiedBy(6);

        $this->assertEquals($result, true);
    }

    public function testOrConditionSecondFails()
    {
        $less = new LessThan(5);
        $greater = new GreaterThan(3);
        $condition = new OrCondition($less, $greater);

        $result = $condition->isSatisfiedBy(2);

        $this->assertEquals($result, true);
    }

    public function testOrConditionBothFail()
    {
        $less = new LessThan(5);
        $greater = new GreaterThan(7);
        $condition = new OrCondition($less, $greater);

        $result = $condition->isSatisfiedBy(6);

        $this->assertEquals($result, false);
    }

    public function testComplexConditionSucceeds()
    {
        $condition = SatisfiedByConditionType::condition();
        $condition->myInt = new LessThan(5);

        $storable = new SatisfiedByConditionType();
        $storable->myInt = 4;

        $result = $condition->isSatisfiedBy($storable);

        $this->assertEquals($result, true);
    }

    public function testComplexConditionFails()
    {
        $condition = SatisfiedByConditionType::condition();
        $condition->myInt = new LessThan(5);

        $storable = new SatisfiedByConditionType();
        $storable->myInt = 6;

        $result = $condition->isSatisfiedBy($storable);

        $this->assertEquals($result, false);
    }

    public function testComplexConditionTwoFieldsBothSucceed()
    {
        $condition = SatisfiedByConditionType::condition();
        $condition->myInt = new LessThan(5);
        $condition->myText = "abc";

        $storable = new SatisfiedByConditionType();
        $storable->myInt = 4;
        $storable->myText = "abc";

        $result = $condition->isSatisfiedBy($storable);

        $this->assertEquals($result, true);
    }

    public function testComplexConditionTwoFieldsFirstFails()
    {
        $condition = SatisfiedByConditionType::condition();
        $condition->myInt = new LessThan(5);
        $condition->myText = "abc";

        $storable = new SatisfiedByConditionType();
        $storable->myInt = 6;
        $storable->myText = "abc";

        $result = $condition->isSatisfiedBy($storable);

        $this->assertEquals($result, false);
    }

    public function testComplexConditionTwoFieldsSecondFails()
    {
        $condition = SatisfiedByConditionType::condition();
        $condition->myInt = new LessThan(5);
        $condition->myText = "abc";

        $storable = new SatisfiedByConditionType();
        $storable->myInt = 4;
        $storable->myText = "abcd";

        $result = $condition->isSatisfiedBy($storable);

        $this->assertEquals($result, false);
    }

    public function testComplexConditionTwoFieldsBothFail()
    {
        $condition = SatisfiedByConditionType::condition();
        $condition->myInt = new LessThan(5);
        $condition->myText = "abc";

        $storable = new SatisfiedByConditionType();
        $storable->myInt = 6;
        $storable->myText = "abcd";

        $result = $condition->isSatisfiedBy($storable);

        $this->assertEquals($result, false);
    }

    public function testHasAConditionSucceeds()
    {
        $less = new LessThan(5);
        $hasA = new HasA($less);
        $condition = SatisfiedByConditionType::condition();
        $condition->myInts = $hasA;

        $storable = new SatisfiedByConditionType();

        $storable->myInts->add(6);
        $storable->myInts->add(4);

        $result = $condition->isSatisfiedBy($storable);

        $this->assertEquals($result, true);
    }

    public function testHasAConditionFails()
    {
        $less = new LessThan(5);
        $hasA = new HasA($less);
        $condition = SatisfiedByConditionType::condition();
        $condition->myInts = $hasA;

        $storable = new SatisfiedByConditionType();

        $storable->myInts->add(6);
        $storable->myInts->add(8);

        $result = $condition->isSatisfiedBy($storable);

        $this->assertEquals($result, false);
    }

    public function testHasAConditionEmptyCollection()
    {
        $less = new LessThan(5);
        $hasA = new HasA($less);
        $condition = SatisfiedByConditionType::condition();
        $condition->myInts = $hasA;

        $storable = new SatisfiedByConditionType();

        $result = $condition->isSatisfiedBy($storable);

        $this->assertEquals($result, false);
    }

    public function testHasOnlyConditionSucceeds()
    {
        $less = new LessThan(5);
        $hasOnly = new HasOnly($less);
        $condition = SatisfiedByConditionType::condition();
        $condition->myInts = $hasOnly;

        $storable = new SatisfiedByConditionType();

        $storable->myInts->add(2);
        $storable->myInts->add(4);

        $result = $condition->isSatisfiedBy($storable);

        $this->assertEquals($result, true);
    }

    public function testHasOnlyConditionFails()
    {
        $less = new LessThan(5);
        $hasOnly = new HasOnly($less);
        $condition = SatisfiedByConditionType::condition();
        $condition->myInts = $hasOnly;

        $storable = new SatisfiedByConditionType();

        $storable->myInts->add(6);
        $storable->myInts->add(4);

        $result = $condition->isSatisfiedBy($storable);

        $this->assertEquals($result, false);
    }

    public function testHasOnlyConditionEmptyCollection()
    {
        $less = new LessThan(5);
        $hasOnly = new HasOnly($less);
        $condition = SatisfiedByConditionType::condition();
        $condition->myInts = $hasOnly;

        $storable = new SatisfiedByConditionType();

        $result = $condition->isSatisfiedBy($storable);

        $this->assertEquals($result, true);
    }

    public function testInvalidNullEqualToArray()
    {
        $condition = new EqualTo(null);

        $this->expectException("Exception");

        $result = $condition->isSatisfiedBy([]);
    }

    public function testInvalidNullNotEqualToArray()
    {
        $condition = new NotEqualTo(null);

        $this->expectException("Exception");

        $result = $condition->isSatisfiedBy([]);
    }

    public function testInvalidDateTimeEqualToInt()
    {
        $condition = new EqualTo(new DateTimeImmutable());

        $this->expectException("Exception");

        $result = $condition->isSatisfiedBy(2);
    }

    public function testInvalidDateTimeNotEqualToInt()
    {
        $condition = new NotEqualTo(new DateTimeImmutable());

        $this->expectException("Exception");

        $result = $condition->isSatisfiedBy(2);
    }

    public function testInvalidDateTimeLessThanInt()
    {
        $condition = new LessThan(new DateTimeImmutable());

        $this->expectException("Exception");

        $result = $condition->isSatisfiedBy(2);
    }

    public function testInvalidDateTimeLessOrEqualInt()
    {
        $condition = new LessOrEqual(new DateTimeImmutable());

        $this->expectException("Exception");

        $result = $condition->isSatisfiedBy(2);
    }

    public function testInvalidDateTimeGreaterThanInt()
    {
        $condition = new GreaterThan(new DateTimeImmutable());

        $this->expectException("Exception");

        $result = $condition->isSatisfiedBy(2);
    }

    public function testInvalidDateTimeGreaterOrEqualToInt()
    {
        $condition = new GreaterOrEqual(new DateTimeImmutable());

        $this->expectException("Exception");

        $result = $condition->isSatisfiedBy(2);
    }

    public function testInvalidStorableEqualToInt()
    {
        $condition = new EqualTo(new SatisfiedByConditionType());

        $this->expectException("Exception");

        $result = $condition->isSatisfiedBy(2);
    }

    public function testInvalidStorableNotEqualToInt()
    {
        $condition = new NotEqualTo(new SatisfiedByConditionType());

        $this->expectException("Exception");

        $result = $condition->isSatisfiedBy(2);
    }

    public function testInvalidIntEqualToText()
    {
        $condition = new EqualTo(1);

        $this->expectException("Exception");

        $result = $condition->isSatisfiedBy("a");
    }

    public function testInvalidIntNotEqualToText()
    {
        $condition = new NotEqualTo(2);

        $this->expectException("Exception");

        $result = $condition->isSatisfiedBy("a");
    }

    public function testInvalidIntLessThanText()
    {
        $condition = new LessThan(3);

        $this->expectException("Exception");

        $result = $condition->isSatisfiedBy("a");
    }

    public function testInvalidIntLessOrEqualText()
    {
        $condition = new LessOrEqual(4);

        $this->expectException("Exception");

        $result = $condition->isSatisfiedBy("a");
    }

    public function testInvalidIntGreaterThanText()
    {
        $condition = new GreaterThan(5);

        $this->expectException("Exception");

        $result = $condition->isSatisfiedBy("a");
    }

    public function testInvalidIntGreaterOrEqualToText()
    {
        $condition = new GreaterOrEqual(6);

        $this->expectException("Exception");

        $result = $condition->isSatisfiedBy("a");
    }

    public function testInvalidHasAOnText()
    {
        $condition = new HasA("a");

        $this->expectException("Exception");

        $result = $condition->isSatisfiedBy("a");
    }

    public function testInvalidHasOnlyOnText()
    {
        $condition = new HasOnly("a");

        $this->expectException("Exception");

        $result = $condition->isSatisfiedBy("a");
    }

    public function testInvalidAndCondition()
    {
        $text = new EqualTo("a");
        $number = new EqualTo(1);

        $condition = new AndCondition($text, $number);

        $this->expectException("Exception");

        $result = $condition->isSatisfiedBy("a");
    }

    public function testInvalidOrCondition()
    {
        $text = new EqualTo("a");
        $number = new EqualTo(1);

        $condition = new OrCondition($text, $number);

        $this->expectException("Exception");

        $result = $condition->isSatisfiedBy("b");
    }
}

?>
