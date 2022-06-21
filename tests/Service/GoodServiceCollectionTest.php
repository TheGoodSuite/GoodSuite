<?php

// Making abstract so I can have a child that does exactly this, and one
// that does repeat these tests with a modifier, to confirm everything is
// still working with that modifier
// (PHPUnit doesn't like it if I do this without the base class being
//  abstract)

/**
 * @runTestsInSeparateProcesses
 */
class GoodServiceCollectionTest extends \PHPUnit\Framework\TestCase
{
    private function assertNoExceptions()
    {
        // Make assertion so test isn't marked as risky
        $this->assertTrue(true);
    }

    protected function compile($subDirectory)
    {
        $service = new \Good\Service\Service([
            "modifiers" => [],
            "inputDir" => dirname(__FILE__) . '/../testInputFiles/GoodService/GoodServiceCollectionTest/' . $subDirectory,
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

    public function testDatetimeCollectionProperty()
    {
        $this->compile('datetimeCollection');

        $myType = new MyType();

        $this->assertInstanceOf(\Good\Service\Collection::class, $myType->myArray);
    }

    public function testBooleanCollectionProperty()
    {
        $this->compile('booleanCollection');

        $myType = new MyType();

        $this->assertInstanceOf(\Good\Service\Collection::class, $myType->myArray);
    }

    public function testFloatCollectionProperty()
    {
        $this->compile('floatCollection');

        $myType = new MyType();

        $this->assertInstanceOf(\Good\Service\Collection::class, $myType->myArray);
    }

    public function testIntCollectionProperty()
    {
        $this->compile('intCollection');

        $myType = new MyType();

        $this->assertInstanceOf(\Good\Service\Collection::class, $myType->myArray);
    }

    public function testTextCollectionProperty()
    {
        $this->compile('textCollection');

        $myType = new MyType();

        $this->assertInstanceOf(\Good\Service\Collection::class, $myType->myArray);
    }

    public function testReferenceCollectionProperty()
    {
        $this->compile('referenceCollection');

        $myType = new MyType();

        $this->assertInstanceOf(\Good\Service\Collection::class, $myType->myArray);
    }

    public function testDatetimeCollectionPropertyCorrectValue()
    {
        $this->compile('datetimeCollection');

        $myType = new MyType();
        $myType->myArray->add(new DateTimeImmutable());

        $this->assertNoExceptions();
    }

    public function testDatetimeCollectionPropertyIncorrectValue()
    {
        $this->compile('datetimeCollection');

        $this->expectException(\Good\Service\InvalidParameterException::class);

        $myType = new MyType();
        $myType->myArray->add(4);
    }

    public function testBooleanCollectionPropertyCorrectValue()
    {
        $this->compile('booleanCollection');

        $myType = new MyType();
        $myType->myArray->add(false);

        $this->assertNoExceptions();
    }

    public function testBooleanCollectionPropertyIncorrectValue()
    {
        $this->compile('booleanCollection');

        $this->expectException(\Good\Service\InvalidParameterException::class);

        $myType = new MyType();
        $myType->myArray->add(4);
    }

    public function testFloatCollectionPropertyCorrectValue()
    {
        $this->compile('floatCollection');

        $myType = new MyType();
        $myType->myArray->add(3.4);

        $this->assertNoExceptions();
    }

    public function testFloatCollectionPropertyIncorrectValue()
    {
        $this->compile('floatCollection');

        $this->expectException(\Good\Service\InvalidParameterException::class);

        $myType = new MyType();
        $myType->myArray->add("AAA");
    }

    public function testIntCollectionPropertyCorrectValue()
    {
        $this->compile('intCollection');

        $myType = new MyType();
        $myType->myArray->add(4);

        $this->assertNoExceptions();
    }

    public function testIntCollectionPropertyIncorrectValue()
    {
        $this->compile('intCollection');

        $this->expectException(\Good\Service\InvalidParameterException::class);

        $myType = new MyType();
        $myType->myArray->add(4.2);
    }

    public function testTextCollectionPropertyCorrectValue()
    {
        $this->compile('textCollection');

        $myType = new MyType();
        $myType->myArray->add("aaa");

        $this->assertNoExceptions();
    }

    public function testTextCollectionPropertyIncorrectValue()
    {
        $this->compile('textCollection');

        $this->expectException(\Good\Service\InvalidParameterException::class);

        $myType = new MyType();
        $myType->myArray->add(22);
    }

    public function testIntCollectionPropertyTypeModifierCorrectValue()
    {
        $this->compile('intCollectionWithTypeModifier');

        $myType = new MyType();
        $myType->myArray->add(4);

        $this->assertNoExceptions();
    }

    public function testIntCollectionPropertyTypeModifierIncorrectValue()
    {
        $this->compile('intCollectionWithTypeModifier');

        $this->expectException(\Good\Service\InvalidParameterException::class);

        $myType = new MyType();
        $myType->myArray->add(11);
    }

    public function testTextCollectionPropertyTypeModifierCorrectValue()
    {
        $this->compile('textCollectionWithTypeModifier');

        $myType = new MyType();
        $myType->myArray->add("aa");

        $this->assertNoExceptions();
    }

    public function testTextCollectionPropertyTypeModifierIncorrectValue()
    {
        $this->compile('textCollectionWithTypeModifier');

        $this->expectException(\Good\Service\InvalidParameterException::class);

        $myType = new MyType();
        $myType->myArray->add("aaa");
    }

    public function testTextCollectionForeach()
    {
        $this->compile('intCollection');

        $myType = new MyType();

        $myType->myArray->add(1);
        $myType->myArray->add(3);
        $myType->myArray->add(2);

        $expected = [1, 3, 2];

        $i = 0;

        foreach ($myType->myArray as $myInt)
        {
            $this->assertSame($expected[$i], $myInt);

            $i++;
        }
    }
}

?>
