<?php

// Making abstract so I can have a child that does exactly this, and one
// that does repeat these tests with a modifier, to confirm everything is
// still working with that modifier
// (PHPUnit doesn't like it if I do this without the base class being
//  abstract)

/**
 * @runTestsInSeparateProcesses
 */
abstract class GoodServiceBaseTest extends \PHPUnit\Framework\TestCase
{
    private $files = array(); // so we know what to clean up
    protected $inputDir = '';
    private $outputDir = '';

    private function assertNoExceptions()
    {
        // Make assertion so test isn't marked as risky
        $this->assertTrue(true);
    }

    public function setUp(): void
    {
        $this->inputDir = dirname(__FILE__) . '/../testInputFiles/';
        $this->outputDir =  dirname(__FILE__) . '/../generated/';
    }

    protected function compile($inputfiles, $modifiers = array())
    {
        $rolemodel = new \Good\Rolemodel\Rolemodel();

        $schema = $rolemodel->createSchema($inputfiles);

        $service = new \Good\Service\Service();

        $outputFiles = $service->compile($modifiers, $schema, $this->outputDir);

        foreach ($outputFiles as $file)
        {
            require $file;
        }

        $this->files = array_merge($this->files, $inputfiles, $outputFiles);

        $this->files[] = $this->outputDir . 'GeneratedBaseClass.php';
    }

    public function tearDown(): void
    {
        foreach($this->files as $file)
        {
            unlink($file);
        }

        // not strictly necessary, but I'd rather not rely ont he fact
        // that each test runs in a different instance fo this class
        $this->files = array();
    }

    public function testIntTypeModifierMinValueAboveMaxValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            "datatype MyType{ int(minValue=42, maxValue=10) myInt;}");

        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile(array($this->inputDir . 'MyType.datatype'));
    }

    public function testIntTypeModifierNonNegativeAndMinValueNegative()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            "datatype MyType{ int(minValue=-1, nonNegative) myInt;}");

        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile(array($this->inputDir . 'MyType.datatype'));
    }

    public function testIntTypeModifierNonNegativeAndMaxValueNegative()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            "datatype MyType{ int(maxValue=-1, nonNegative) myInt;}");

        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile(array($this->inputDir . 'MyType.datatype'));
    }

    public function testIntProperty()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myInt = 5;

        $this->assertEquals($myType->myInt, 5);
        $this->assertIsInt($myType->myInt);
    }

    /**
     * @depends testIntProperty
     */
    public function testIntGetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myInt = 5;

        $this->assertEquals($myType->getMyInt(), 5);
        $this->assertIsInt($myType->myInt);
    }

    public function testIntPropertyNonIntValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myInt = 5.5;
    }

    public function testIntPropertyObjectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myInt = new MyType();
    }

    public function testIntPropertyBelowNegativeMinValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(minValue=-1) myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myInt = -5;
    }

    public function testIntPropertyBelowPositiveMinValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(minValue=17) myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myInt = 5;
    }

    public function testIntPropertyAboveNegativeMaxValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(maxValue=-3) myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myInt = -1;
    }

    public function testIntPropertyAbovePositiveMaxValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(maxValue=17) myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myInt = 42;
    }

    public function testIntPropertyNegativeValueWithNonNegativeTypeModifier()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(nonNegative) myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myInt = -1;
    }

    public function testIntPropertyAboveNegativeMinValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(minValue=-2) myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myInt = -1;

        $this->assertNoExceptions();
    }

    public function testIntPropertyAbovePositiveMinValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(minValue=2) myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myInt = 5;

        $this->assertNoExceptions();
    }

    public function testIntPropertyBelowNegativeMaxValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(maxValue=-3) myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myInt = -5;

        $this->assertNoExceptions();
    }

    public function testIntPropertyBelowPositiveMaxValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(maxValue=512) myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myInt = 42;

        $this->assertNoExceptions();
    }

    public function testIntPropertyBetweenNegativeMinValueAndMaxValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(minValue=-200, maxValue=-43) myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myInt = -100;

        $this->assertNoExceptions();
    }

    public function testIntPropertyBetweenPositiveMinValueAndMaxValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(minValue=2, maxValue=5) myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myInt = 4;

        $this->assertNoExceptions();
    }

    public function testIntPropertyBetweenNegativeMinValueAndPositiveMaxValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(minValue=-512, maxValue=512) myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myInt = 0;

        $this->assertNoExceptions();
    }

    public function testIntPropertyPositiveValueWithNonNegativeTypeModifier()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(nonNegative) myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myInt = 2;

        $this->assertNoExceptions();
    }

    public function testFloatProperty()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { float myFloat; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myFloat = 5.5;

        $this->assertEquals($myType->myFloat, 5.5);
        $this->assertIsFloat($myType->myFloat);
    }

    /**
     * @depends testFloatProperty
     */
    public function testFloatGetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { float myFloat; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myFloat = 5.5;

        $this->assertEquals($myType->getMyFloat(), 5.5);
        $this->assertIsFloat($myType->myFloat);
    }

    public function testFloatPropertyNonFloatValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { float myFloat; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myFloat = 5;
    }

    public function testFloatPropertyObjectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { float myFloat; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myFloat = new MyType();
    }

    public function testTextTypeModifierMinLengthAboveMaxLength()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            "datatype MyType{ text(minLength=42, maxLength=10) myText;}");

        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile(array($this->inputDir . 'MyType.datatype'));
    }

    public function testTextTypeModifierMinLengthNegative()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            "datatype MyType{ text(minLength=-1) myText;}");

        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile(array($this->inputDir . 'MyType.datatype'));
    }

    public function testTextTypeModifierMaxLengthNegative()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            "datatype MyType{ text(maxLength=-1) myText;}");

        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile(array($this->inputDir . 'MyType.datatype'));
    }

    public function testTextTypeModifierSepcifiedBothLengthAndMinLength()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            "datatype MyType{ text(length=3, minLength=1) myText;}");

        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile(array($this->inputDir . 'MyType.datatype'));
    }

    public function testTextTypeModifierSepcifiedBothLengthAndMaxLength()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            "datatype MyType{ text(length=3, maxLength=3) myText;}");

        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile(array($this->inputDir . 'MyType.datatype'));
    }

    public function testTextProperty()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text myText; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myText = "Hello World!";

        $this->assertEquals($myType->myText, "Hello World!");
        $this->assertIsString($myType->myText);
    }

    /**
     * @depends testTextProperty
     */
    public function testTextGetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text myText; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myText = "Hello World!";

        $this->assertEquals($myType->getMyText(), "Hello World!");
        $this->assertIsString($myType->myText);
    }

    public function testTextPropertyNonTextValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text myText; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myText = 5;
    }

    public function testTextPropertyObjectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text myText; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myText = new MyType();
    }

    public function testTextPropertyShorterThanMinLength()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text(minLength=3) myText; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myText = "A";
    }

    public function testTextPropertyLongerThanMaxLength()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text(maxLength=5) myText; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myText = "123456";
    }

    public function testTextPropertyShorterThanLength()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text(length=3) myText; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myText = "A";
    }

    public function testTextPropertyLongerThanLength()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text(length=5) myText; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myText = "123456";
    }

    public function testTextPropertyLongerThanMinLength()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text(minLength=3) myText; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myText = "ABCD";

        $this->assertNoExceptions();
    }

    public function testTextPropertyShorterThanMaxLength()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text(maxLength=5) myText; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myText = "123";

        $this->assertNoExceptions();
    }

    public function testTextPropertyValueLengthBetweenMinLengthAndMaxLength()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text(minLength=2, maxLength=6) myText; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myText = "1234";

        $this->assertNoExceptions();
    }

    public function testTextPropertyAsLongAsLength()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text(length=3) myText; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myText = "AAA";

        $this->assertNoExceptions();
    }

    public function testDatetimeProperty()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { datetime myDatetime; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $date = new \DateTime();
        $myType->myDatetime = $date;

        $this->assertEquals($myType->myDatetime, $date);
        $this->assertInstanceOf('\\DateTime', $myType->myDatetime);
    }

    public function testDatetimePropertyNonDatetimeValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { datetime myDatetime; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myDatetime = 5;
    }

    public function testDatetimePropertyObjectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { datetime myDatetime; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myDatetime = new MyType();
    }

    public function testReferenceProperty()
    {
        file_put_contents($this->inputDir . 'ReferencedType.datatype',
                            'datatype ReferencedType { int justAnInt; }');

        file_put_contents($this->inputDir . 'ReferenceType.datatype',
                            'datatype ReferenceType { "ReferencedType" reference; }');

        $this->compile(array($this->inputDir . 'ReferencedType.datatype',
                             $this->inputDir . 'ReferenceType.datatype'));

        $reference = new ReferencedType();
        $reference->justAnInt = 45;

        $referer = new ReferenceType();
        $referer->reference = $reference;

        $this->assertEquals($referer->reference, $reference);
        $this->assertInstanceOf('ReferencedType', $referer->reference);
    }

    /**
     * @depends testDatetimeProperty
     */
    public function testDatetimeGetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { datetime myDatetime; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $date = new \DateTime();
        $myType->myDatetime = $date;

        $this->assertEquals($myType->getMyDatetime(), $date);
        $this->assertInstanceOf('\\DateTime', $myType->myDatetime);
    }

    /**
     * @depends testReferenceProperty
     */
    public function testReferenceGetter()
    {
        file_put_contents($this->inputDir . 'ReferencedType.datatype',
                            'datatype ReferencedType { int justAnInt; }');

        file_put_contents($this->inputDir . 'ReferenceType.datatype',
                            'datatype ReferenceType { "ReferencedType" reference; }');

        $this->compile(array($this->inputDir . 'ReferencedType.datatype',
                             $this->inputDir . 'ReferenceType.datatype'));

        $reference = new ReferencedType();
        $reference->justAnInt = 45;

        $referer = new ReferenceType();
        $referer->reference = $reference;

        $this->assertEquals($referer->getReference(), $reference);
        $this->assertInstanceOf('ReferencedType', $referer->reference);
    }

    /**
     * @depends testIntProperty
     */
    public function testIntSetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->setMyInt(5);

        $this->assertEquals($myType->myInt, 5);
        $this->assertIsInt($myType->myInt);
    }

    /**
     * @depends testFloatProperty
     */
    public function testFloatSetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { float myFloat; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->setMyFloat(5.5);

        $this->assertEquals($myType->myFloat, 5.5);
        $this->assertIsFloat($myType->myFloat);
    }

    /**
     * @depends testTextProperty
     */
    public function testTextSetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text myText; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->setMyText("Hello World!");

        $this->assertEquals($myType->myText, "Hello World!");
        $this->assertIsString($myType->myText);
    }

    /**
     * @depends testDatetimeProperty
     */
    public function testDatetimeSetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { datetime myDatetime; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $date = new \DateTime();
        $myType->setMyDatetime($date);

        $this->assertEquals($myType->myDatetime, $date);
        $this->assertInstanceOf('\\DateTime', $myType->myDatetime);
    }

    /**
     * @depends testReferenceProperty
     */
    public function testReferenceSetter()
    {
        file_put_contents($this->inputDir . 'ReferencedType.datatype',
                            'datatype ReferencedType { int justAnInt; }');

        file_put_contents($this->inputDir . 'ReferenceType.datatype',
                            'datatype ReferenceType { "ReferencedType" reference; }');

        $this->compile(array($this->inputDir . 'ReferencedType.datatype',
                             $this->inputDir . 'ReferenceType.datatype'));

        $reference = new ReferencedType();
        $reference->justAnInt = 45;

        $referer = new ReferenceType();
        $referer->setReference($reference);

        $this->assertEquals($referer->reference, $reference);
        $this->assertInstanceOf('ReferencedType', $referer->reference);
    }

    /**
     * @depends testIntProperty
     */
    public function testPrivateGetterSetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { [private] int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        // Private is actually protected...
        $reflection = new \ReflectionMethod('MyType', 'getMyInt');
        $this->assertTrue($reflection->isProtected());
        $reflection = new \ReflectionMethod('MyType', 'setMyInt');
        $this->assertTrue($reflection->isProtected());
    }

    /**
     * @depends testIntProperty
     */
    public function testThereIsNoPrivatePropertyGet()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { [private] int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        // Private is actually protected...
        $val = new MyType();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown or non-public property');
        $int = $val->myInt;
    }

    /**
     * @depends testIntProperty
     */
    public function testThereIsNoPrivatePropertySet()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { [private] int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        // Private is actually protected...
        $val = new MyType();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown or non-public property');
        $val->myInt = 4;
    }

    /**
     * @depends testIntProperty
     */
    public function testProtectedGetterSetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { [protected] int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $reflection = new \ReflectionMethod('MyType', 'getMyInt');
        $this->assertTrue($reflection->isProtected());
        $reflection = new \ReflectionMethod('MyType', 'setMyInt');
        $this->assertTrue($reflection->isProtected());
    }

    /**
     * @depends testIntProperty
     */
    public function testThereIsNoProtectedPropertyGet()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { [private] int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        // Private is actually protected...
        $val = new MyType();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown or non-public property');
        $int = $val->myInt;
    }

    /**
     * @depends testIntProperty
     */
    public function testThereIsNoProtectedPropertySet()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { [private] int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        // Private is actually protected...
        $val = new MyType();

        $this->expectException('Exception');
        $this->expectExceptionMessage('Unknown or non-public property');
        $val->myInt = 4;
    }

    /**
     * @depends testIntProperty
     */
    public function testPublicGetterSetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { [public] int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        // Private is actually protected...
        $reflection = new \ReflectionMethod('MyType', 'getMyInt');
        $this->assertTrue($reflection->isPublic());
        $reflection = new \ReflectionMethod('MyType', 'setMyInt');
        $this->assertTrue($reflection->isPublic());
    }

    /**
     * @depends testIntProperty
     */
    public function testDefaultIsPublicGetterSetter()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        // Private is actually protected...
        $reflection = new \ReflectionMethod('MyType', 'getMyInt');
        $this->assertTrue($reflection->isPublic());
        $reflection = new \ReflectionMethod('MyType', 'setMyInt');
        $this->assertTrue($reflection->isPublic());
    }

    public function testMultipleMembers()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            "datatype MyType\n".
                            "{\n".
                            "   int myInt;\n" .
                            "   float myFloat;\n" .
                            "   int myInt2;\n" .
                            "}");
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myInt = 5;
        $this->assertEquals($myType->myInt, 5);
        $this->assertIsInt($myType->myInt);

        $myType = new MyType();
        $myType->myFloat = 5.5;
        $this->assertEquals($myType->myFloat, 5.5);
        $this->assertIsFloat($myType->myFloat);

        $myType = new MyType();
        $myType->myInt2 = 42;
        $this->assertEquals($myType->myInt2, 42);
        $this->assertIsInt($myType->myInt2);
    }

    public function testMultipleMembersOnOneLine()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            "datatype MyType{int myInt;".
                            "[]float myFloat;".
                            "int myInt2;}");
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myInt = 5;
        $this->assertEquals($myType->myInt, 5);
        $this->assertIsInt($myType->myInt);

        $myType = new MyType();
        $myType->myFloat = 5.5;
        $this->assertEquals($myType->myFloat, 5.5);
        $this->assertIsFloat($myType->myFloat);

        $myType = new MyType();
        $myType->myInt2 = 42;
        $this->assertEquals($myType->myInt2, 42);
        $this->assertIsInt($myType->myInt2);
    }

    public function testOneMemberSpreadOverMultipleLines()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            "datatype\n" .
                            "MyType\n" .
                            "{\n" .
                            "[\n" .
                            "public\n".
                            "]\n".
                            "int   \n".
                            "myInt\n" .
                            ";\n" .
                            "}\n");
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myInt = 5;
        $this->assertEquals($myType->myInt, 5);
        $this->assertIsInt($myType->myInt);
    }

    public function testMultipleDatatypesInOneFileOnOneLine()
    {
        file_put_contents($this->inputDir . 'MyTypes.datatype',
                            "datatype MyType{[public]int myInt;}datatype MyOtherType{float myFloat;}");
        $this->compile(array($this->inputDir . 'MyTypes.datatype'));

        $myType = new MyType();
        $myType->myInt = 5;
        $this->assertEquals($myType->myInt, 5);
        $this->assertIsInt($myType->myInt);

        $myType = new MyOtherType();
        $myType->myFloat = 5.5;
        $this->assertEquals($myType->myFloat, 5.5);
        $this->assertIsFloat($myType->myFloat);
    }

    public function testMultipleDatatypesInOneFileOnMultipleLines()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            "datatype\n" .
                            "MyType\n" .
                            "{\n" .
                            "[\n" .
                            "public\n".
                            "]\n".
                            "int   \n".
                            "myInt\n" .
                            ";\n" .
                            "}\n" .
                            "\n" .
                            "\n" .
                            "\n" .
                            "datatype\n" .
                            "MyOtherType\n" .
                            "{\n" .
                            "[\n" .
                            "public\n".
                            "]\n".
                            "float   \n".
                            "myFloat\n" .
                            ";\n" .
                            "}\n");
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $myType = new MyType();
        $myType->myInt = 5;
        $this->assertEquals($myType->myInt, 5);
        $this->assertIsInt($myType->myInt);
    }

    public function testUnknownTypeModifierWithoutValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(unknown) myInt; }');

        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile(array($this->inputDir . 'MyType.datatype'));
    }

    public function testUnknownTypeModifierWithValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(unknown=3) myInt; }');

        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile(array($this->inputDir . 'MyType.datatype'));
    }

    public function testTypeModifierThatShouldHaveValueWithoutValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(maxValue) myInt; }');

        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile(array($this->inputDir . 'MyType.datatype'));
    }

    public function testTypeModifierThatShouldNotHaveValueWithValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(nonNegative=42) myInt; }');

        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile(array($this->inputDir . 'MyType.datatype'));
    }

    public function testTypeWithoutTypeModifiersButWithBraces()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int() myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->assertNoExceptions();
    }

    public function testTypeWithoutTypeModifiersButWithBracesWithWhitespace()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(      ) myInt; }');
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->assertNoExceptions();
    }

    public function testTypeModifiersWithABunchOfWhitespace()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            "datatype MyType { int(  minValue   =    4     ,    nonNegative   , maxValue    = 5  ) myInt; }");
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->assertNoExceptions();
    }

    public function testTypeModifiersWithoutAnyWhitespace()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            "datatype MyType{int(minValue=4,nonNegative,maxValue=5) myInt;}");
        $this->compile(array($this->inputDir . 'MyType.datatype'));

        $this->assertNoExceptions();
    }
}

?>
