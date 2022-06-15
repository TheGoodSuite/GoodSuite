<?php

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class GoodMannersCollectionTest extends \PHPUnit\Framework\TestCase
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
        file_put_contents(dirname(__FILE__) . '/../testInputFiles/CollectionType.datatype',
                                                                            "datatype CollectionType\n" .
                                                                            "{" .
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
    }

    public function tearDown(): void
    {
        $this->_tearDownAfterClass();
    }

    public function testIntCollectionSetFromArray()
    {
        $myCollectionType = new CollectionType();

        $myCollectionType->setFromArray(["myInts" => [1, 2, 3]]);

        $expected = [1, 2, 3];
        $i = 0;

        foreach ($myCollectionType->myInts as $myInt)
        {
            $this->assertSame($expected[$i], $myInt);

            $i++;
        }
    }

    public function testIntCollectionToArrayTypecasting()
    {
        $myCollectionType = new CollectionType();

        $myCollectionType->setFromArray(["myInts" => ['1']]);

        foreach ($myCollectionType->myInts as $myInt)
        {
            $this->assertIsInt($myInt);
            $this->assertSame(1, $myInt);
        }
    }

    public function testFloatCollectionSetFromArray()
    {
        $myCollectionType = new CollectionType();

        $myCollectionType->setFromArray(["myFloats" => [1.1, 2.2, 3.3]]);

        $expected = [1.1, 2.2, 3.3];
        $i = 0;

        foreach ($myCollectionType->myFloats as $myFloat)
        {
            $this->assertSame($expected[$i], $myFloat);

            $i++;
        }
    }

    public function testFloatCollectionToArrayTypecasting()
    {
        $myCollectionType = new CollectionType();

        $myCollectionType->setFromArray(["myFloats" => ['1.1']]);

        foreach ($myCollectionType->myFloats as $myFloat)
        {
            $this->assertIsFloat($myFloat);
            $this->assertSame(1.1, $myFloat);
        }
    }

    public function testTextCollectionSetFromArray()
    {
        $myCollectionType = new CollectionType();

        $myCollectionType->setFromArray(["myTexts" => ["a", "c", "b"]]);

        $expected = ["a", "c", "b"];
        $i = 0;

        foreach ($myCollectionType->myTexts as $myText)
        {
            $this->assertSame($expected[$i], $myText);

            $i++;
        }
    }

    public function testTextCollectionToArrayTypecasting()
    {
        $myCollectionType = new CollectionType();

        $myCollectionType->setFromArray(["myTexts" => [1]]);

        foreach ($myCollectionType->myTexts as $myText)
        {
            $this->assertIsString($myText);
            $this->assertSame("1", $myText);
        }
    }

    public function testDatetimeCollectionSetFromArray()
    {
        $now = new DateTimeImmutable();

        $myCollectionType = new CollectionType();

        $myCollectionType->setFromArray(["myDatetimes" => [$now]]);

        foreach ($myCollectionType->myDatetimes as $myDatetime)
        {
            $this->assertSame($now, $myDatetime);
        }
    }

    public function testDatetimeCollectionToArrayParseDate()
    {
        // We need to set the milliseconds to zero because those are trimmed in
        // ATOM format. We also set the hours and minutes just because that's
        // the easiest way to achieve setting the milliseconds to zero
        $today = (new DateTimeImmutable())->setTime(1, 1);

        $myCollectionType = new CollectionType();

        $myCollectionType->setFromArray(["myDatetimes" => [$today->format(DateTimeImmutable::ATOM)]]);

        foreach ($myCollectionType->myDatetimes as $myDatetime)
        {
            $this->assertInstanceOf(\DateTimeImmutable::class, $myDatetime);
            $this->assertEquals($today, $myDatetime);
        }
    }

    public function testDatetimeCollectionToArrayNotParseNull()
    {
        $myCollectionType = new CollectionType();

        $myCollectionType->setFromArray(["myDatetimes" => [null]]);

        foreach ($myCollectionType->myDatetimes as $myDatetime)
        {
            $this->assertEquals(null, $myDatetime);
        }
    }

    public function testReferenceCollectionSetFromArray()
    {
        $myCollectionType = new CollectionType();

        $myCollectionType->setFromArray(["myReferences" => [$myCollectionType]]);

        foreach ($myCollectionType->myReferences as $myReference)
        {
            $this->assertSame($myCollectionType, $myReference);
        }
    }

    public function testCollectionToArray()
    {
        $myCollectionType = new CollectionType();

        $myCollectionType->setId('11');
        $myCollectionType->myInts->add(1);
        $myCollectionType->myInts->add(2);
        $myCollectionType->myInts->add(3);

        $expected = [1, 2, 3];
        $i = 0;

        $array = $myCollectionType->toArray(false);
        $this->assertIsArray($array['myInts']);

        foreach ($array['myInts'] as $myInt)
        {
            $this->assertSame($expected[$i], $myInt);

            $i++;
        }
    }

    public function testDatetimeCollectionToArrayFormatting()
    {
        $now = new DateTimeImmutable();

        $myCollectionType = new CollectionType();

        $myCollectionType->setId('11');
        $myCollectionType->myDatetimes->add($now);

        $array = $myCollectionType->toArray(true);
        $this->assertIsArray($array['myDatetimes']);

        foreach ($array['myDatetimes'] as $myDatetime)
        {
            $this->assertSame($now->format(DateTimeImmutable::ATOM), $myDatetime);
        }
    }

    public function testDatetimeCollectionToArrayUnformattedWhenDateToIsoIsFalse()
    {
        $now = new DateTimeImmutable();

        $myCollectionType = new CollectionType();

        $myCollectionType->setId('11');
        $myCollectionType->myDatetimes->add($now);

        $array = $myCollectionType->toArray(false);
        $this->assertIsArray($array['myDatetimes']);

        foreach ($array['myDatetimes'] as $myDatetime)
        {
            $this->assertSame($now, $myDatetime);
        }
    }

    public function testDReferenceCollectionToArrayFormatting()
    {
        $myReference = new CollectionType();
        $myReference->setId('22');

        $myCollectionType = new CollectionType();

        $myCollectionType->setId('11');
        $myCollectionType->myReferences->add($myReference);

        $array = $myCollectionType->toArray(false);
        $this->assertIsArray($array['myReferences']);

        foreach ($array['myReferences'] as $myReference)
        {
            $this->assertIsArray($myReference);
        }
    }
}

?>
