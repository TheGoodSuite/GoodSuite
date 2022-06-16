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
    private function assertNoExceptions()
    {
        // Make assertion so test isn't marked as risky
        $this->assertTrue(true);
    }

    protected function getModifiers()
    {
        return [];
    }

    protected function compile($subDirectory)
    {
        $service = new \Good\Service\Service([
            "modifiers" => $this->getModifiers(),
            "inputDir" => dirname(__FILE__) . '/../testInputFiles/GoodServiceBaseTest/' . $subDirectory,
            "outputDir" => dirname(__FILE__) . '/../generated/'
        ]);

        $service->load();
    }

    public function tearDown(): void
    {
        $path = dirname(__FILE__) . '/../generated/';
        $iterator = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
        $recursiveIterator = new \RecursiveIteratorIterator($iterator);

        foreach ($recursiveIterator as $file)
        {
            if ($file->getExtension() === 'php')
            {
                unlink($file->getPathname());
            }
        }
    }

    public function testIntTypeModifierMinValueAboveMaxValue()
    {
        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile('modifierMinValueAboveMaxValue');
    }

    public function testIntTypeModifierNonNegativeAndMinValueNegative()
    {
        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile('modifierNonNegativeAndMinValueNegative');
    }

    public function testIntTypeModifierNonNegativeAndMaxValueNegative()
    {
        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile('modifierNonNegativeAndMaxValueNegative');
    }

    public function testIntProperty()
    {
        $this->compile('intProperty');

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
        $this->compile('intProperty');

        $myType = new MyType();
        $myType->myInt = 5;

        $this->assertEquals($myType->getMyInt(), 5);
        $this->assertIsInt($myType->myInt);
    }

    public function testIntPropertyNonIntValue()
    {
        $this->compile('intProperty');

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myInt = 5.5;
    }

    public function testIntPropertyObjectValue()
    {
        $this->compile('intProperty');

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myInt = new MyType();
    }

    public function testIntPropertyBelowNegativeMinValue()
    {
        $this->compile('negativeMinValue');

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myInt = -5;
    }

    public function testIntPropertyBelowPositiveMinValue()
    {
        $this->compile('positiveMinValue');

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myInt = 5;
    }

    public function testIntPropertyAboveNegativeMaxValue()
    {
        $this->compile('negativeMaxValue');

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myInt = -1;
    }

    public function testIntPropertyAbovePositiveMaxValue()
    {
        $this->compile('positiveMaxValue');

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myInt = 42;
    }

    public function testIntPropertyNegativeValueWithNonNegativeTypeModifier()
    {
        $this->compile('nonNegative');

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myInt = -1;
    }

    public function testIntPropertyAboveNegativeMinValue()
    {
        $this->compile('negativeMinValue');

        $myType = new MyType();
        $myType->myInt = 0;

        $this->assertNoExceptions();
    }

    public function testIntPropertyAbovePositiveMinValue()
    {
        $this->compile('positiveMinValue');

        $myType = new MyType();
        $myType->myInt = 18;

        $this->assertNoExceptions();
    }

    public function testIntPropertyBelowNegativeMaxValue()
    {
        $this->compile('negativeMaxValue');

        $myType = new MyType();
        $myType->myInt = -5;

        $this->assertNoExceptions();
    }

    public function testIntPropertyBelowPositiveMaxValue()
    {
        $this->compile('positiveMaxValue');

        $myType = new MyType();
        $myType->myInt = 1;

        $this->assertNoExceptions();
    }

    public function testIntPropertyBetweenNegativeMinValueAndMaxValue()
    {
        $this->compile('negativeMinValueAndMaxValue');

        $myType = new MyType();
        $myType->myInt = -100;

        $this->assertNoExceptions();
    }

    public function testIntPropertyBetweenPositiveMinValueAndMaxValue()
    {
        $this->compile('positiveMinValueAndMaxValue');

        $myType = new MyType();
        $myType->myInt = 4;

        $this->assertNoExceptions();
    }

    public function testIntPropertyBetweenNegativeMinValueAndPositiveMaxValue()
    {
        $this->compile('negativeMinValueAndPositiveMaxValue');

        $myType = new MyType();
        $myType->myInt = 0;

        $this->assertNoExceptions();
    }

    public function testIntPropertyPositiveValueWithNonNegativeTypeModifier()
    {
        $this->compile('nonNegative');

        $myType = new MyType();
        $myType->myInt = 2;

        $this->assertNoExceptions();
    }

    public function testFloatProperty()
    {
        $this->compile('floatProperty');

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
        $this->compile('floatProperty');

        $myType = new MyType();
        $myType->myFloat = 5.5;

        $this->assertEquals($myType->getMyFloat(), 5.5);
        $this->assertIsFloat($myType->myFloat);
    }

    public function testFloatPropertyIntValue()
    {
        $this->compile('floatProperty');

        $myType = new MyType();
        $myType->myFloat = 5;

        $this->assertNoExceptions();
    }

    public function testFloatPropertyNonFloatValue()
    {
        $this->compile('floatProperty');

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myFloat = "aa";
    }

    public function testFloatPropertyObjectValue()
    {
        $this->compile('floatProperty');

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myFloat = new MyType();
    }

    public function testTextTypeModifierMinLengthAboveMaxLength()
    {
        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile('modifierMinLengthAboveMaxLength');
    }

    public function testTextTypeModifierMinLengthNegative()
    {
        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile('modifierMinLengthNegative');
    }

    public function testTextTypeModifierMaxLengthNegative()
    {
        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile('modifierMaxLengthNegative');
    }

    public function testTextTypeModifierSepcifiedBothLengthAndMinLength()
    {
        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile('modifierSepcifiedBothLengthAndMinLength');
    }

    public function testTextTypeModifierSepcifiedBothLengthAndMaxLength()
    {
        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile('modifierSepcifiedBothLengthAndMaxLength');
    }

    public function testTextProperty()
    {
        $this->compile('textProperty');

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
        $this->compile('textProperty');

        $myType = new MyType();
        $myType->myText = "Hello World!";

        $this->assertEquals($myType->getMyText(), "Hello World!");
        $this->assertIsString($myType->myText);
    }

    public function testTextPropertyNonTextValue()
    {
        $this->compile('textProperty');

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myText = 5;
    }

    public function testTextPropertyObjectValue()
    {
        $this->compile('textProperty');

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myText = new MyType();
    }

    public function testTextPropertyShorterThanMinLength()
    {
        $this->compile('minLength');

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myText = "A";
    }

    public function testTextPropertyLongerThanMaxLength()
    {
        $this->compile('maxLength');

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myText = "123456";
    }

    public function testTextPropertyShorterThanLength()
    {
        $this->compile('length');

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myText = "A";
    }

    public function testTextPropertyLongerThanLength()
    {
        $this->compile('length');

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myText = "123456";
    }

    public function testTextPropertyLongerThanMinLength()
    {
        $this->compile('minLength');

        $myType = new MyType();
        $myType->myText = "ABCD";

        $this->assertNoExceptions();
    }

    public function testTextPropertyShorterThanMaxLength()
    {
        $this->compile('maxLength');

        $myType = new MyType();
        $myType->myText = "123";

        $this->assertNoExceptions();
    }

    public function testTextPropertyValueLengthBetweenMinLengthAndMaxLength()
    {
        $this->compile('minLengthAndMaxLength');

        $myType = new MyType();
        $myType->myText = "1234";

        $this->assertNoExceptions();
    }

    public function testTextPropertyAsLongAsLength()
    {
        $this->compile('length');

        $myType = new MyType();
        $myType->myText = "AAA";

        $this->assertNoExceptions();
    }

    public function testDatetimeProperty()
    {
        $this->compile('datetimeProperty');

        $myType = new MyType();
        $date = new \DateTimeImmutable();
        $myType->myDatetime = $date;

        $this->assertEquals($myType->myDatetime, $date);
        $this->assertInstanceOf('\\DateTimeImmutable', $myType->myDatetime);
    }

    public function testDatetimePropertyNonDatetimeValue()
    {
        $this->compile('datetimeProperty');

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myDatetime = 5;
    }

    public function testDatetimePropertyObjectValue()
    {
        $this->compile('datetimeProperty');

        $this->expectException("Good\Service\InvalidParameterException");

        $myType = new MyType();
        $myType->myDatetime = new MyType();
    }

    public function testReferenceProperty()
    {
        $this->compile('referenceProperty');

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
        $this->compile('datetimeProperty');

        $myType = new MyType();
        $date = new \DateTimeImmutable();
        $myType->myDatetime = $date;

        $this->assertEquals($myType->getMyDatetime(), $date);
        $this->assertInstanceOf('\\DateTimeImmutable', $myType->myDatetime);
    }

    /**
     * @depends testReferenceProperty
     */
    public function testReferenceGetter()
    {
        $this->compile('referenceProperty');

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
        $this->compile('intProperty');

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
        $this->compile('floatProperty');

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
        $this->compile('textProperty');

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
        $this->compile('datetimeProperty');

        $myType = new MyType();
        $date = new \DateTimeImmutable();
        $myType->setMyDatetime($date);

        $this->assertEquals($myType->myDatetime, $date);
        $this->assertInstanceOf('\\DateTimeImmutable', $myType->myDatetime);
    }

    /**
     * @depends testReferenceProperty
     */
    public function testReferenceSetter()
    {
        $this->compile('referenceProperty');

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
        $this->compile('privateProperty');

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
        $this->compile('privateProperty');

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
        $this->compile('privateProperty');

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
        $this->compile('protectedProperty');

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
        $this->compile('protectedProperty');

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
        $this->compile('protectedProperty');

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
        $this->compile('publicProperty');

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
        $this->compile('intProperty');

        // Private is actually protected...
        $reflection = new \ReflectionMethod('MyType', 'getMyInt');
        $this->assertTrue($reflection->isPublic());
        $reflection = new \ReflectionMethod('MyType', 'setMyInt');
        $this->assertTrue($reflection->isPublic());
    }

    public function testMultipleMembers()
    {
        $this->compile('multipleMembers');

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
        $this->compile('multipleMembersOnOneLine');

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
        $this->compile('oneMemberSpreadOverMultipleLines');

        $myType = new MyType();
        $myType->myInt = 5;
        $this->assertEquals($myType->myInt, 5);
        $this->assertIsInt($myType->myInt);
    }

    public function testMultipleDatatypesInOneFileOnOneLine()
    {
        $this->compile('multipleDatatypesInOneFileOnOneLine');

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
        $this->compile('multipleDatatypesInOneFileOnMultipleLines');

        $myType = new MyType();
        $myType->myInt = 5;
        $this->assertEquals($myType->myInt, 5);
        $this->assertIsInt($myType->myInt);
    }

    public function testUnknownTypeModifierWithoutValue()
    {
        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile('unknownTypeModifierWithoutValue');
    }

    public function testUnknownTypeModifierWithValue()
    {
        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile('unknownTypeModifierWithValue');
    }

    public function testTypeModifierThatShouldHaveValueWithoutValue()
    {
        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile('modifierThatShouldHaveValueWithoutValue');
    }

    public function testTypeModifierThatShouldNotHaveValueWithValue()
    {
        $this->expectException("Good\Rolemodel\InvalidTypeModifierException");

        $this->compile('modifierThatShouldNotHaveValueWithValue');
    }

    public function testTypeWithoutTypeModifiersButWithBraces()
    {
        $this->compile('typeWithoutTypeModifiersButWithBraces');

        $this->assertNoExceptions();
    }

    public function testTypeWithoutTypeModifiersButWithBracesWithWhitespace()
    {
        $this->compile('typeWithoutTypeModifiersButWithBracesWithWhitespace');

        $this->assertNoExceptions();
    }

    public function testTypeModifiersWithABunchOfWhitespace()
    {
        $this->compile('typeModifiersWithABunchOfWhitespace');

        $this->assertNoExceptions();
    }

    public function testTypeModifiersWithoutAnyWhitespace()
    {
        $this->compile('typeModifiersWithoutAnyWhitespace');

        $this->assertNoExceptions();
    }

    public function testDuplicateDatatype()
    {
        $this->expectException("Exception");

        $this->compile('duplicateDatatype');
    }
}

?>
