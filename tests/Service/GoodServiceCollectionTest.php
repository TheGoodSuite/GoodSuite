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

    public function setUp(): void
    {
        $this->inputDir = dirname(__FILE__) . '/../testInputFiles/';
        $this->outputDir =  dirname(__FILE__) . '/../generated/';
    }

    protected function compile($types, $modifiers = null, $inputFiles = null)
    {
        if ($modifiers == null)
        {
            $modifiers = [];
        }

        $rolemodel = new \Good\Rolemodel\Rolemodel();

        if ($inputFiles == null)
        {
            $inputFiles = [];

            foreach ($types as $type)
            {
                $inputFiles[] = $this->inputDir . $type . '.datatype';
            }
        }

        $service = new \Good\Service\Service();
        $service->autocompile(dirname(__FILE__) . '/../testInputFiles/', dirname(__FILE__) . '/../generated/', $modifiers);

        $this->files = array_merge($this->files, $inputFiles);

        $file = $this->outputDir . 'GeneratedBaseClass.php';
        $this->files[] = $file;

        foreach ($types as $type)
        {
            $file = $this->outputDir . $type . '.datatype.php';
            $this->files[] = $file;

            $file = $this->outputDir . $type . 'Resolver.php';

            if (file_exists($file))
            {
                $this->files[] = $file;
            }
        }
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

    public function testDatetimeCollectionProperty()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { datetime[] myArray; }');
        $this->compile(array('MyType'));

        $myType = new MyType();

        $this->assertInstanceOf(\Good\Service\Collection::class, $myType->myArray);
    }

    public function testFloatCollectionProperty()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { float[] myArray; }');
        $this->compile(array('MyType'));

        $myType = new MyType();

        $this->assertInstanceOf(\Good\Service\Collection::class, $myType->myArray);
    }

    public function testIntCollectionProperty()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int[] myArray; }');
        $this->compile(array('MyType'));

        $myType = new MyType();

        $this->assertInstanceOf(\Good\Service\Collection::class, $myType->myArray);
    }

    public function testTextCollectionProperty()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text[] myArray; }');
        $this->compile(array('MyType'));

        $myType = new MyType();

        $this->assertInstanceOf(\Good\Service\Collection::class, $myType->myArray);
    }

    public function testReferenceCollectionProperty()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { "MyType"[] myArray; }');
        $this->compile(array('MyType'));

        $myType = new MyType();

        $this->assertInstanceOf(\Good\Service\Collection::class, $myType->myArray);
    }

    public function testDatetimeCollectionPropertyCorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { datetime[] myArray; }');
        $this->compile(array('MyType'));

        $myType = new MyType();
        $myType->myArray->add(new DateTimeImmutable());

        $this->assertNoExceptions();
    }

    public function testDatetimeCollectionPropertyIncorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { datetime[] myArray; }');
        $this->compile(array('MyType'));

        $this->expectException(\Good\Service\InvalidParameterException::class);

        $myType = new MyType();
        $myType->myArray->add(4);
    }

    public function testFloatCollectionPropertyCorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { float[] myArray; }');
        $this->compile(array('MyType'));

        $myType = new MyType();
        $myType->myArray->add(3.4);

        $this->assertNoExceptions();
    }

    public function testFloatCollectionPropertyIncorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { float[] myArray; }');
        $this->compile(array('MyType'));

        $this->expectException(\Good\Service\InvalidParameterException::class);

        $myType = new MyType();
        $myType->myArray->add("AAA");
    }

    public function testIntCollectionPropertyCorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int[] myArray; }');
        $this->compile(array('MyType'));

        $myType = new MyType();
        $myType->myArray->add(4);

        $this->assertNoExceptions();
    }

    public function testIntCollectionPropertyIncorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int[] myArray; }');
        $this->compile(array('MyType'));

        $this->expectException(\Good\Service\InvalidParameterException::class);

        $myType = new MyType();
        $myType->myArray->add(4.2);
    }

    public function testTextCollectionPropertyCorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text[] myArray; }');
        $this->compile(array('MyType'));

        $myType = new MyType();
        $myType->myArray->add("aaa");

        $this->assertNoExceptions();
    }

    public function testTextCollectionPropertyIncorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text[] myArray; }');
        $this->compile(array('MyType'));

        $this->expectException(\Good\Service\InvalidParameterException::class);

        $myType = new MyType();
        $myType->myArray->add(22);
    }

    public function testIntCollectionPropertyTypeModifierCorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(maxValue=10)[] myArray; }');
        $this->compile(array('MyType'));

        $myType = new MyType();
        $myType->myArray->add(4);

        $this->assertNoExceptions();
    }

    public function testIntCollectionPropertyTypeModifierIncorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int(maxValue=10)[] myArray; }');
        $this->compile(array('MyType'));

        $this->expectException(\Good\Service\InvalidParameterException::class);

        $myType = new MyType();
        $myType->myArray->add(11);
    }

    public function testTextCollectionPropertyTypeModifierCorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text(maxLength=2)[] myArray; }');
        $this->compile(array('MyType'));

        $myType = new MyType();
        $myType->myArray->add("aa");

        $this->assertNoExceptions();
    }

    public function testTextCollectionPropertyTypeModifierIncorrectValue()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { text(maxLength=2)[] myArray; }');
        $this->compile(array('MyType'));

        $this->expectException(\Good\Service\InvalidParameterException::class);

        $myType = new MyType();
        $myType->myArray->add("aaa");
    }

    public function testTextCollectionForeach()
    {
        file_put_contents($this->inputDir . 'MyType.datatype',
                            'datatype MyType { int[] myArray; }');
        $this->compile(array('MyType'));

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
