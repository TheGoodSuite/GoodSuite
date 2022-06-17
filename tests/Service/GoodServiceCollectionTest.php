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
    private $files = array(); // so we know what to clean up
    protected $inputDir = '';
    private $outputDir = '';

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
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { datetime[] myArray; }');
        $this->compile('datetimeCollection');

        $myType = new MyType();

        $this->assertInstanceOf(\Good\Service\Collection::class, $myType->myArray);
    }

    public function testFloatCollectionProperty()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { float[] myArray; }');
        $this->compile('floatCollection');

        $myType = new MyType();

        $this->assertInstanceOf(\Good\Service\Collection::class, $myType->myArray);
    }

    public function testIntCollectionProperty()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int[] myArray; }');
        $this->compile('intCollection');

        $myType = new MyType();

        $this->assertInstanceOf(\Good\Service\Collection::class, $myType->myArray);
    }

    public function testTextCollectionProperty()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text[] myArray; }');
        $this->compile('textCollection');

        $myType = new MyType();

        $this->assertInstanceOf(\Good\Service\Collection::class, $myType->myArray);
    }

    public function testReferenceCollectionProperty()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { "MyType"[] myArray; }');
        $this->compile('referenceCollection');

        $myType = new MyType();

        $this->assertInstanceOf(\Good\Service\Collection::class, $myType->myArray);
    }

    public function testDatetimeCollectionPropertyCorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { datetime[] myArray; }');
        $this->compile('datetimeCollection');

        $myType = new MyType();
        $myType->myArray->add(new DateTimeImmutable());

        $this->assertNoExceptions();
    }

    public function testDatetimeCollectionPropertyIncorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { datetime[] myArray; }');
        $this->compile('datetimeCollection');

        $this->expectException(\Good\Service\InvalidParameterException::class);

        $myType = new MyType();
        $myType->myArray->add(4);
    }

    public function testFloatCollectionPropertyCorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { float[] myArray; }');
        $this->compile('floatCollection');

        $myType = new MyType();
        $myType->myArray->add(3.4);

        $this->assertNoExceptions();
    }

    public function testFloatCollectionPropertyIncorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { float[] myArray; }');
        $this->compile('floatCollection');

        $this->expectException(\Good\Service\InvalidParameterException::class);

        $myType = new MyType();
        $myType->myArray->add("AAA");
    }

    public function testIntCollectionPropertyCorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int[] myArray; }');
        $this->compile('intCollection');

        $myType = new MyType();
        $myType->myArray->add(4);

        $this->assertNoExceptions();
    }

    public function testIntCollectionPropertyIncorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int[] myArray; }');
        $this->compile('intCollection');

        $this->expectException(\Good\Service\InvalidParameterException::class);

        $myType = new MyType();
        $myType->myArray->add(4.2);
    }

    public function testTextCollectionPropertyCorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text[] myArray; }');
        $this->compile('textCollection');

        $myType = new MyType();
        $myType->myArray->add("aaa");

        $this->assertNoExceptions();
    }

    public function testTextCollectionPropertyIncorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text[] myArray; }');
        $this->compile('textCollection');

        $this->expectException(\Good\Service\InvalidParameterException::class);

        $myType = new MyType();
        $myType->myArray->add(22);
    }

    public function testIntCollectionPropertyTypeModifierCorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(maxValue=10)[] myArray; }');
        $this->compile('intCollectionWithTypeModifier');

        $myType = new MyType();
        $myType->myArray->add(4);

        $this->assertNoExceptions();
    }

    public function testIntCollectionPropertyTypeModifierIncorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(maxValue=10)[] myArray; }');
        $this->compile('intCollectionWithTypeModifier');

        $this->expectException(\Good\Service\InvalidParameterException::class);

        $myType = new MyType();
        $myType->myArray->add(11);
    }

    public function testTextCollectionPropertyTypeModifierCorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text(maxLength=2)[] myArray; }');
        $this->compile('textCollectionWithTypeModifier');

        $myType = new MyType();
        $myType->myArray->add("aa");

        $this->assertNoExceptions();
    }

    public function testTextCollectionPropertyTypeModifierIncorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text(maxLength=2)[] myArray; }');
        $this->compile('textCollectionWithTypeModifier');

        $this->expectException(\Good\Service\InvalidParameterException::class);

        $myType = new MyType();
        $myType->myArray->add("aaa");
    }

    public function testTextCollectionForeach()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int[] myArray; }');
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
