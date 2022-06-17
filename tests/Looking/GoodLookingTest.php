<?php

use Good\Looking\Looking;

class GoodLookingTest extends \PHPUnit\Framework\TestCase
{
    private $baseInputFilesDir = __dir__ . '/../testInputFiles/GoodLooking/GoodLookingTest/';

    public function tearDown(): void
    {
        $path = $this->baseInputFilesDir;
        $iterator = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
        $recursiveIterator = new \RecursiveIteratorIterator($iterator);

        foreach ($recursiveIterator as $file)
        {
            if ($file->getExtension() === 'compiledTemplate')
            {
                unlink($file->getPathname());
            }
        }
    }


    public function testTextOnly()
    {
        $this->expectOutputString("content\n");

        $goodLooking = new Looking($this->baseInputFilesDir . 'textOnly.template');
        $goodLooking->display();
    }

    public function testOutputStringVariable()
    {
        $this->expectOutputString('content');

        $goodLooking = new Looking($this->baseInputFilesDir . 'variable.template');
        $goodLooking->registerVar('var', 'content');
        $goodLooking->display();
    }

    public function testOutputIntVariable()
    {
        $this->expectOutputString('12345');

        $goodLooking = new Looking($this->baseInputFilesDir . 'variable.template');
        $goodLooking->registerVar('var', 12345);

        $goodLooking->display();
    }

    public function testOutputFloatVariable()
    {
        // issue #28
        $this->expectOutputString('123.456');

        $goodLooking = new Looking($this->baseInputFilesDir . 'variable.template');
        $goodLooking->registerVar('var', 123.456);
        $goodLooking->display();
    }

    public function testOutputStringLiteral()
    {
        $this->expectOutputString('content');

        $goodLooking = new Looking($this->baseInputFilesDir . 'stringLiteral.template');
        $goodLooking->display();
    }

    public function testOutputIntLiteral()
    {
        $this->expectOutputString('12345');

        $goodLooking = new Looking($this->baseInputFilesDir . 'intLiteral.template');
        $goodLooking->display();
    }

    public function testOutputFloatLiteral()
    {
        $this->expectOutputString('123.456');

        $goodLooking = new Looking($this->baseInputFilesDir . 'floatLiteral.template');
        $goodLooking->display();
    }

    /**
     * @depends testOutputStringLiteral
     */
    public function testMultipleStatementsInBlock()
    {
        $this->expectOutputString('YES OR NO');

        $goodLooking = new Looking($this->baseInputFilesDir . 'multipleStatementsInBlock.template');
        $goodLooking->display();
    }

    public function testIfTrueLiteral()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'ifTrue.template');
        $goodLooking->display();
    }

    public function testIfFalseLiteral()
    {
        $this->expectOutputString('');

        $goodLooking = new Looking($this->baseInputFilesDir . 'ifFalse.template');
        $goodLooking->display();
    }

    public function testIfTrueVariable()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'ifVariable.template');
        $goodLooking->registerVar('var', true);
        $goodLooking->display();
    }

    public function testIfFalseVariable()
    {
        $this->expectOutputString('');

        $goodLooking = new Looking($this->baseInputFilesDir . 'ifVariable.template');
        $goodLooking->registerVar('var', false);
        $goodLooking->display();
    }

    public function testIfElseTrue()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'ifElse.template');
        $goodLooking->registerVar('var', true);
        $goodLooking->display();
    }

    /**
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testDoubleIfOnOneLine()
    {
        // issue #29  (now fixed)

        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'doubleIfOneLine.template');
        $goodLooking->registerVar('var', true);
        $goodLooking->display();
    }

    public function testIfElseFalse()
    {
        $this->expectOutputString('NO');

        $goodLooking = new Looking($this->baseInputFilesDir . 'ifElse.template');
        $goodLooking->registerVar('var', false);
        $goodLooking->display();
    }

    public function testForrangeUpwards()
    {
        $this->expectOutputString('YES YES YES YES YES ');

        $goodLooking = new Looking($this->baseInputFilesDir . 'forRange.template');
        $goodLooking->registerVar('a', 1);
        $goodLooking->registerVar('b', 5);
        $goodLooking->display();
    }

    public function testForrangeDownwards()
    {
        $this->expectOutputString('YES YES YES YES YES ');

        $goodLooking = new Looking($this->baseInputFilesDir . 'forRange.template');
        $goodLooking->registerVar('a', 5);
        $goodLooking->registerVar('b', 1);
        $goodLooking->display();
    }

    public function testForeach()
    {
        $this->expectOutputString('YES NO MAYBE ');

        $goodLooking = new Looking($this->baseInputFilesDir . 'forEach.template');
        $goodLooking->registerVar('words', array('YES', 'NO', 'MAYBE'));
        $goodLooking->display();
    }


    /**
     * @depends testMultipleStatementsInBlock
     */
    public function testEmptyStatement()
    {
        $this->expectOutputString('');

        $goodLooking = new Looking($this->baseInputFilesDir . 'emptyStatement.template');
        $goodLooking->display();
    }

    /**
     * @depends testOutputStringLiteral
     */
    public function testArrayAccessStringKey()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'arrayStringKeyAccess.template');
        $goodLooking->registerVar('arr', array('bla' => 'YES'));
        $goodLooking->display();
    }

    /**
     * @depends testOutputStringLiteral
     */
    public function testArrayAccessIntKey()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'arrayIntKeyAccess.template');
        $goodLooking->registerVar('arr', array('YES'));
        $goodLooking->display();
    }

    /**
     * @depends testOutputStringLiteral
     */
    public function testArrayAccessStringVariableKey()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'arrayVarKeyAccess.template');
        $goodLooking->registerVar('arr', array('blu' => 'NO',
                                               'bla' => 'YES'));
        $goodLooking->registerVar('key', 'bla');
        $goodLooking->display();
    }

    /**
     * @depends testOutputStringLiteral
     */
    public function testArrayAccessIntVariableKey()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'arrayVarKeyAccess.template');
        $goodLooking->registerVar('arr', array('YES',
                                               'NO'));
        $goodLooking->registerVar('key', 0);
        $goodLooking->display();
    }

    /**
     * @depends testOutputStringVariable
     */
    public function testRegisterMultipleVars()
    {
        $this->expectOutputString('YESNO');

        $goodLooking = new Looking($this->baseInputFilesDir . 'twoVars.template');
        $goodLooking->registerMultipleVars(array('a' => 'YES',
                                                 'b' => 'NO'));
        $goodLooking->display();
    }

    /**
     * @depends testOutputIntLiteral
     */
    public function testAdditionOperator()
    {
        $this->expectOutputString('2');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/addition.template');
        $goodLooking->display();
    }


    /**
     * @depends testOutputIntLiteral
     */
    public function testSubtractionOperator()
    {
        $this->expectOutputString('0');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/subtraction.template');
        $goodLooking->display();
    }


    /**
     * @depends testOutputIntLiteral
     */
    public function testDivisionOperator()
    {
        $this->expectOutputString('2');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/division.template');
        $goodLooking->display();
    }


    /**
     * @depends testOutputIntLiteral
     */
    public function testMultiplicationOperator()
    {
        $this->expectOutputString('6');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/multiplication.template');
        $goodLooking->display();
    }


    /**
     * @depends testOutputStringLiteral
     */
    public function testConcatenationOperator()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/concatenation.template');
        $goodLooking->display();
    }

    /**
     * @depends testIfTrueLiteral
     * @depends testDoubleIfOnOneLine
     */
    public function testEqualityOperator()
    {
        $this->expectOutputString('YES-YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/equality.template');
        $goodLooking->display();
    }

    /**
     * @depends testIfTrueLiteral
     * @depends testDoubleIfOnOneLine
     */
    public function testOtherEqualityOperator()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/strictEquality.template');
        $goodLooking->display();
    }

    /**
     * @depends testIfTrueLiteral
     * @depends testDoubleIfOnOneLine
     */
    public function testInequalityOperator()
    {
        $this->expectOutputString('NO-NO');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/inequality.template');
        $goodLooking->display();
    }

    /**
     * @depends testIfTrueLiteral
     * @depends testDoubleIfOnOneLine
     */
    public function testOtherInequalityOperator()
    {
        $this->expectOutputString('NO-YES-NO');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/strictInequality.template');
        $goodLooking->display();
    }

    /**
     * @depends testIfTrueLiteral
     * @depends testDoubleIfOnOneLine
     */
    public function testModulusOperator()
    {
        $this->expectOutputString('2');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/modulus.template');
        $goodLooking->display();
    }

    /**
     * @depends testIfTrueLiteral
     * @depends testDoubleIfOnOneLine
     */
    public function testLargerThanOperator()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/largerThan.template');
        $goodLooking->display();
    }

    /**
     * @depends testIfTrueLiteral
     * @depends testDoubleIfOnOneLine
     */
    public function testLessThanOperator()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/lessThan.template');
        $goodLooking->display();
    }

    /**
     * @depends testIfTrueLiteral
     * @depends testDoubleIfOnOneLine
     */
    public function testLessThanOrEqualsOperator()
    {
        $this->expectOutputString('YESMAYBE');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/largerOrEqual.template');
        $goodLooking->display();
    }

    /**
     * @depends testIfTrueLiteral
     * @depends testDoubleIfOnOneLine
     */
    public function testLargerThanOrEqualsOperator()
    {
        $this->expectOutputString('YESMAYBE');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/lessOrEqual.template');
        $goodLooking->display();
    }

    /**
     * @depends testOutputIntLiteral
     * @depends testAdditionOperator
     * @depends testDivisionOperator
     * @depends testSubtractionOperator
     */
    public function testMathematicalOperationsPriotity()
    {
        $this->expectOutputString('2');
        // would be 1 if prioritized purely according to order

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/operatorPriority.template');
        $goodLooking->display();
    }

    /**
     * @depends testOutputIntLiteral
     * @depends testAdditionOperator
     * @depends testDivisionOperator
     * @depends testSubtractionOperator
     */
    public function testMathematicalOperationsPriotityWithParentheses()
    {
        $this->expectOutputString('1');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/operatorPriorityWithParentheses.template');
        $goodLooking->display();
    }

    /*
     * @depends testIfTrueLiteral
     */
    public function testAndTT()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/and.template');
        $goodLooking->registerVar('a', true);
        $goodLooking->registerVar('b', true);
        $goodLooking->display();
    }

    /*
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testAndTF()
    {
        $this->expectOutputString('');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/and.template');
        $goodLooking->registerVar('a', true);
        $goodLooking->registerVar('b', false);
        $goodLooking->display();
    }

    /*
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testAndFT()
    {
        $this->expectOutputString('');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/and.template');
        $goodLooking->registerVar('a', false);
        $goodLooking->registerVar('b', true);
        $goodLooking->display();
    }

    /*
     * @depends testIfFalseLiteral
     */
    public function testAndFF()
    {
        $this->expectOutputString('');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/and.template');
        $goodLooking->registerVar('a', false);
        $goodLooking->registerVar('b', false);
        $goodLooking->display();
    }

    /*
     * @depends testIfTrueLiteral
     */
    public function testAlternateAndTT()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/alternateAnd.template');
        $goodLooking->registerVar('a', true);
        $goodLooking->registerVar('b', true);
        $goodLooking->display();
    }

    /*
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testAlternateAndTF()
    {
        $this->expectOutputString('');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/alternateAnd.template');
        $goodLooking->registerVar('a', true);
        $goodLooking->registerVar('b', false);
        $goodLooking->display();
    }

    /*
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testAlternateAndFT()
    {
        $this->expectOutputString('');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/alternateAnd.template');
        $goodLooking->registerVar('a', false);
        $goodLooking->registerVar('b', true);
        $goodLooking->display();
    }

    /*
     * @depends testIfFalseLiteral
     */
    public function testAlternateAndFF()
    {
        $this->expectOutputString('');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/alternateAnd.template');
        $goodLooking->registerVar('a', false);
        $goodLooking->registerVar('b', false);
        $goodLooking->display();
    }

    /*
     * @depends testIfTrueLiteral
     */
    public function testOrTT()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/or.template');
        $goodLooking->registerVar('a', true);
        $goodLooking->registerVar('b', true);
        $goodLooking->display();
    }

    /*
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testOrTF()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/or.template');
        $goodLooking->registerVar('a', true);
        $goodLooking->registerVar('b', false);
        $goodLooking->display();
    }

    /*
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testOrFT()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/or.template');
        $goodLooking->registerVar('a', false);
        $goodLooking->registerVar('b', true);
        $goodLooking->display();
    }

    /*
     * @depends testIfFalseLiteral
     */
    public function testOrFF()
    {
        $this->expectOutputString('');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/or.template');
        $goodLooking->registerVar('a', false);
        $goodLooking->registerVar('b', false);
        $goodLooking->display();
    }

    /*
     * @depends testIfTrueLiteral
     */
    public function testAlternateOrTT()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/alternateOr.template');
        $goodLooking->registerVar('a', true);
        $goodLooking->registerVar('b', true);
        $goodLooking->display();
    }

    /*
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testAlternateOrTF()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/alternateOr.template');
        $goodLooking->registerVar('a', true);
        $goodLooking->registerVar('b', false);
        $goodLooking->display();
    }

    /*
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testAlternateOrFT()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/alternateOr.template');
        $goodLooking->registerVar('a', false);
        $goodLooking->registerVar('b', true);
        $goodLooking->display();
    }

    /*
     * @depends testIfFalseLiteral
     */
    public function testAlternateOrFF()
    {
        $this->expectOutputString('');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/alternateOr.template');
        $goodLooking->registerVar('a', false);
        $goodLooking->registerVar('b', false);
        $goodLooking->display();
    }

    /*
     * @depends testIfTrueLiteral
     * @depends testifFalseLiteral
     */
    public function testXorTT()
    {
        $this->expectOutputString('');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/xor.template');
        $goodLooking->registerVar('a', true);
        $goodLooking->registerVar('b', true);
        $goodLooking->display();
    }

    /*
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testXorTF()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/xor.template');
        $goodLooking->registerVar('a', true);
        $goodLooking->registerVar('b', false);
        $goodLooking->display();
    }

    /*
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testXorFT()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/xor.template');
        $goodLooking->registerVar('a', false);
        $goodLooking->registerVar('b', true);
        $goodLooking->display();
    }

    /*
     * @depends testIfFalseLiteral
     */
    public function testXorFF()
    {
        $this->expectOutputString('');

        $goodLooking = new Looking($this->baseInputFilesDir . 'operator/xor.template');
        $goodLooking->registerVar('a', false);
        $goodLooking->registerVar('b', false);
        $goodLooking->display();
    }

    /*
     * @depends testTextOnly
     */
     public function testComments()
    {
        $this->expectOutputString("YES\n");

        $goodLooking = new Looking($this->baseInputFilesDir . 'comments.template');
        $goodLooking->display();
    }

    /*
     * @depends testOutputStringLiteral
     */
    public function testCommentsInCode()
    {
        $this->expectOutputString('YES');

        $goodLooking = new Looking($this->baseInputFilesDir . 'commentsInCode.template');
        $goodLooking->display();
    }

    /*
     * @depends testIfTrueLiteral
     * @depends testOutputStringLiteral
     * @depends testEmptyStatement
     * @depends testMultipleStatementsInBlock
     * @depends testDoubleIfOnOneLine
     * @depends testForUpwards
     * @depends testForeach
     *
     * tries things like whitespace in strange places and strange capatilization
     */
    public function testControlStructureQuirks()
    {

        $this->expectOutputString('ABBCC');

        $goodLooking = new Looking($this->baseInputFilesDir . 'controlStructureQuirks.template');

        $goodLooking->registerVar('bla', array(0, 1));

        $goodLooking->display();
    }

    /**
     * @depends testOutputStringVariable
     */
    public function testPropertyAccess()
    {
        $this->expectOutputString('bla');

        $goodLooking = new Looking($this->baseInputFilesDir . 'propertyAccess.template');
        $var = new stdClass();
        $var->prop = 'bla';
        $goodLooking->registerVar('var', $var);
        $goodLooking->display();
    }

    /**
     * @depends testPropertyAccess
     */
    public function testPropertyWhitespace1Access()
    {
        $this->expectOutputString('bla');

        $goodLooking = new Looking($this->baseInputFilesDir . 'propertyAccessWithWhitespace.template');
        $var = new stdClass();
        $var->prop = 'bla';
        $goodLooking->registerVar('var', $var);
        $goodLooking->display();
    }

    /**
     * @depends testPropertyAccess
     */
    public function testPropertyWhitespace2Access()
    {
        $this->expectOutputString('bla');

        $goodLooking = new Looking($this->baseInputFilesDir . 'propertyAccessWithOtherWhitespace.template');
        $var = new stdClass();
        $var->prop = 'bla';
        $goodLooking->registerVar('var', $var);
        $goodLooking->display();
    }

    /**
     * @depends testPropertyWhitespace1Access
     * @depends testPropertyWhitespace2Access
     */
    public function testPropertyWhitespace3Access()
    {
        $this->expectOutputString('bla');

        $goodLooking = new Looking($this->baseInputFilesDir . 'propertyAccessWithBothWhitespace.template');
        $var = new stdClass();
        $var->prop = 'bla';
        $goodLooking->registerVar('var', $var);
        $goodLooking->display();
    }

    /**
     * @depends testPropertyAccess
     * @depends testArrayAccessIntKey
     */
    public function testPropertyAndArrayAccessMixed()
    {
        $this->expectOutputString('bla');

        $goodLooking = new Looking($this->baseInputFilesDir . 'propertyArrayAccessMix.template');
        $var = new stdClass();
        $var->prop = 'bla';
        $var = array($var);
        $var2 = new stdClass();
        $var2->prop = $var;
        $var2 = array($var2);

        $goodLooking->registerVar('var', $var2);
        $goodLooking->display();
    }

    public function testElseifFF()
    {
        $this->expectOutputString('C');

        $goodLooking = new Looking($this->baseInputFilesDir . 'elseIf.template');
        $goodLooking->registerVar('a', false);
        $goodLooking->registerVar('b', false);
        $goodLooking->display();
    }

    public function testElseifFT()
    {
        $this->expectOutputString('B');

        $goodLooking = new Looking($this->baseInputFilesDir . 'elseIf.template');
        $goodLooking->registerVar('a', false);
        $goodLooking->registerVar('b', true);
        $goodLooking->display();
    }

    public function testElseifTF()
    {
        $this->expectOutputString('A');

        $goodLooking = new Looking($this->baseInputFilesDir . 'elseIf.template');
        $goodLooking->registerVar('a', true);
        $goodLooking->registerVar('b', false);
        $goodLooking->display();
    }

    public function testElseifTT()
    {
        $this->expectOutputString('A');

        $goodLooking = new Looking($this->baseInputFilesDir . 'elseIf.template');
        $goodLooking->registerVar('a', true);
        $goodLooking->registerVar('b', true);
        $goodLooking->display();
    }

    public function testElseifMany()
    {
        $this->expectOutputString('G');

        $goodLooking = new Looking($this->baseInputFilesDir . 'elseIfMany.template');
        $goodLooking->display();
    }

    public function testElseifWithoutElse()
    {
        $this->expectOutputString('C');

        $goodLooking = new Looking($this->baseInputFilesDir . 'elseIfWithoutElse.template');
        $goodLooking->display();
    }

    public function testInlineControlStructures()
    {
        // control structures without dropping out of code mode, no semicolons

        $this->expectOutputString('AAA');

        $goodLooking = new Looking($this->baseInputFilesDir . 'inlineControlStructures.template');
        $goodLooking->display();
    }

    public function testForrangeAs()
    {
        $this->expectOutputString('456');

        $goodLooking = new Looking($this->baseInputFilesDir . 'forRangeAs.template');
        $goodLooking->display();
    }

    public function testForrangeAsPostLoopZeroBased()
    {
        $this->expectOutputString('7');

        $goodLooking = new Looking($this->baseInputFilesDir . 'forRangeAsPostLoop.template');
        $goodLooking->registerVar('a', 0);
        $goodLooking->registerVar('b', 6);
        $goodLooking->display();
    }

    public function testForrangeAsPostLoopNonZeroBased()
    {
        $this->expectOutputString('7');

        $goodLooking = new Looking($this->baseInputFilesDir . 'forRangeAsPostLoop.template');
        $goodLooking->registerVar('a', 4);
        $goodLooking->registerVar('b', 6);
        $goodLooking->display();
    }

    /*
     * @depends testOutputIntVariable
     */
    public function testVariableAssignment()
    {
        $this->expectOutputString('6');

        $goodLooking = new Looking($this->baseInputFilesDir . 'variableAssignment.template');
        $goodLooking->display();
    }

    /*
     * @depends testOutputIntVariable
     */
    public function testMultipleVariableAssignment()
    {
        $this->expectOutputString('666');

        $goodLooking = new Looking($this->baseInputFilesDir . 'multipleVariableAssignment.template');
        $goodLooking->display();
    }

    /*
     * @depends testOutputIntVariable
     */
    public function testMultipleSeperateVariableAssignment()
    {
        $this->expectOutputString('5610');

        $goodLooking = new Looking($this->baseInputFilesDir . 'multipleSeparateVariableAssignment.template');
        $goodLooking->display();
    }

    /*
     * @depends testOutputIntVariable
     * Issue: #101
     */
    public function testNewlinesBeforeAssignment()
    {
        $this->expectOutputString('5');

        $goodLooking = new Looking($this->baseInputFilesDir . 'newLinesBeforeAssignment.template');
        $goodLooking->display();
    }

    /*
     * @depends testForrangeAs
     */
    public function testForrangeReuse()
    {
        $this->expectOutputString('12a1a2');

        $goodLooking = new Looking($this->baseInputFilesDir . 'forRangeReuse.template');
        $goodLooking->display();
    }

    /*
     * @depends testForreach
     * @depends testArrayAccessIntKey
     */
    public function testForeachReuse()
    {
        $this->expectOutputString('abqaqb');


        $goodLooking = new Looking($this->baseInputFilesDir . 'forEachReuse.template');
        $goodLooking->registerVar('arr', array('a', 'b'));
        $goodLooking->display();
    }

    /*
     * @depends testForrangeAs
     * @depends testForreach
     * @depends testArrayAccessIntKey
     */
    public function testForeachForrangeMixedReuse()
    {
        $this->expectOutputString('abq1q2');

        $goodLooking = new Looking($this->baseInputFilesDir . 'forRangeForEachMixedReuse.template');
        $goodLooking->registerVar('arr', array('a', 'b'));
        $goodLooking->display();
    }

    public function testCallFunction()
    {
        require 'DummyFunctionHandler1.php';

        $this->expectOutputString('ABBA');

        $goodLooking = new Looking($this->baseInputFilesDir . 'callFunction.template');
        $goodLooking->registerFunctionHandler('DummyFunctionHandler1');
        $goodLooking->display();
    }

    public function testCallChainedFunction()
    {
        require 'DummyFunctionHandler2.php';

        $this->expectOutputString('6');

        $goodLooking = new Looking($this->baseInputFilesDir . 'callChainedFunction.template');
        $goodLooking->registerFunctionHandler('DummyFunctionHandler2');
        $goodLooking->display();
    }

    public function testCallFunctionsFromTwoHandlers()
    {
        require 'DummyFunctionHandler3.php';
        require 'DummyFunctionHandler4.php';

        $this->expectOutputString('Handler3Handler4');

        $goodLooking = new Looking($this->baseInputFilesDir . 'callMultipleFunctions.template');
        $goodLooking->registerFunctionHandler('DummyFunctionHandler3');
        $goodLooking->registerFunctionHandler('DummyFunctionHandler4');
        $goodLooking->display();
    }

    public function testCallFunctionMultipleArguments()
    {
        require 'DummyFunctionHandler5.php';

        $this->expectOutputString('6');

        $goodLooking = new Looking($this->baseInputFilesDir . 'callFunctionMultipleArguments.template');
        $goodLooking->registerFunctionHandler('DummyFunctionHandler5');
        $goodLooking->display();
    }

    public function testCallFunctionsThatShareState()
    {
        require 'DummyFunctionHandler6.php';

        $this->expectOutputString('345');

        $goodLooking = new Looking($this->baseInputFilesDir . 'callFunctionsWithSharedState.template');
        $goodLooking->registerFunctionHandler('DummyFunctionHandler6');
        $goodLooking->display();
    }

    public function testAutoEscape()
    {
        $this->expectOutputString('&lt;br&gt;');

        $goodLooking = new Looking($this->baseInputFilesDir . 'variable.template');
        $goodLooking->registerVar('var', "<br>");
        $goodLooking->display();
    }

    public function testArrayLiteral()
    {
        $this->expectOutputString("RWG");

        $goodLooking = new Looking($this->baseInputFilesDir . 'arrayLiteral.template');
        $goodLooking->display();
    }

    public function testArrayLiteralWithExpressiveItems()
    {
        $this->expectOutputString("P8");

        $goodLooking = new Looking($this->baseInputFilesDir . 'arrayLiteralComplex.template');
        $goodLooking->registerVar('r', 'P');
        $goodLooking->display();
    }

    public function testEqualToVariable()
    {
        $this->expectOutputString("YESNO");

        $goodLooking = new Looking($this->baseInputFilesDir . 'equalToVariable.template');
        $goodLooking->registerVar('a', 5);
        $goodLooking->registerVar('b', 'B');
        $goodLooking->display();
    }
}

?>
