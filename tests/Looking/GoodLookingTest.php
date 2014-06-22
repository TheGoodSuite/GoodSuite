<?php

class GoodLookingTest extends PHPUnit_Framework_TestCase
{
    private $template = '';
    
    public static function setUpBeforeClass()
    {
        // PHPUnit is breaking my tests (but not when run in isolation, only when multiple classes are run)
        // through some of the magic it provides when "trying" to be helpful
        // Let's beark into its blacklist to prevent it from doing this!
        $blacklist = new \PHPUnit_Util_Blacklist();
        $refl = new \ReflectionObject($blacklist);
        $method = $refl->getMethod('initialize');
        $method->setAccessible(true);
        $method->invoke($blacklist);
        $prop = $refl->getProperty('directories');
        $prop->setAccessible(true);
        $arr = $prop->getValue();
        $arr[] = realpath(dirname(__FILE__) . '/../testInputFiles/');
        $prop->setValue($arr);
    }
    
    public function setUp()
    {
        $this->template = dirname(__FILE__) . '/../testInputFiles/template';
        file_put_contents($this->template, '');
    }
    
    public function tearDown()
    {
        unlink($this->template);
        unlink($this->template . '.compiledTemplate');
    }
    
    public function testTextOnly()
    {
        $this->expectOutputString('content');
        
        file_put_contents($this->template, 'content');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    public function testOutputStringVariable()
    {
        $this->expectOutputString('content');
        
        file_put_contents($this->template, '<: $var :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerVar('var', 'content');
        $goodLooking->display();
    }
    
    public function testOutputIntVariable()
    {
        $this->expectOutputString('12345');
        
        file_put_contents($this->template, '<: $var :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerVar('var', 12345);
        $goodLooking->display();
    }
    
    public function testOutputFloatVariable()
    {
        // issue #28
        $this->expectOutputString('123.456');
        
        file_put_contents($this->template, '<: $var :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerVar('var', 123.456);
        $goodLooking->display();
    }
    
    public function testOutputStringLiteral()
    {
        $this->expectOutputString('content');
        
        file_put_contents($this->template, '<: "content" :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    public function testOutputIntLiteral()
    {
        $this->expectOutputString('12345');
        file_put_contents($this->template, '<: 12345 :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    public function testOutputFloatLiteral()
    {
        $this->expectOutputString('123.456');
        
        file_put_contents($this->template, '<: 123.456 :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /**
     * @depends testOutputStringLiteral
     */
    public function testMultipleStatementsInBlock()
    {
        $this->expectOutputString('YES OR NO');
        
        file_put_contents($this->template, '<: "YES"; " OR "; "NO" :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    public function testIfTrueLiteral()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if (true): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    public function testIfFalseLiteral()
    {
        $this->expectOutputString('');
        
        file_put_contents($this->template, '<: if (false): :>YES<: endif:>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    public function testIfTrueVariable()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if ($var): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerVar('var', true);
        $goodLooking->display();
    }
    
    public function testIfFalseVariable()
    {
        $this->expectOutputString('');
        
        file_put_contents($this->template, '<: if ($var): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerVar('var', false);
        $goodLooking->display();
    }
    
    public function testIfElseTrue()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if ($var): :>YES<: else: :>NO<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
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
        
        file_put_contents($this->template, '<: if (true): :>YES<: endif:><: if (false)::>NO<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerVar('var', true);
        $goodLooking->display();
    }
    
    public function testIfElseFalse()
    {
        $this->expectOutputString('NO');
        
        file_put_contents($this->template, '<: if ($var): :>YES<: else::>NO<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerVar('var', false);
        $goodLooking->display();
    }
    
    public function testForrangeUpwards()
    {
        $this->expectOutputString('YES YES YES YES YES ');
        
        file_put_contents($this->template, '<: forrange ($a --> $b): :>YES <: endforrange :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerVar('a', 1);
        $goodLooking->registerVar('b', 5);
        $goodLooking->display();
    }
    
    public function testForrangeDownwards()
    {
        $this->expectOutputString('YES YES YES YES YES ');
        
        file_put_contents($this->template, '<: forrange ($a --> $b): :>YES <: endforrange :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerVar('a', 5);
        $goodLooking->registerVar('b', 1);
        $goodLooking->display();
    }
    
    public function testForeach()
    {
        $this->expectOutputString('YES NO MAYBE ');
        
        file_put_contents($this->template, '<: foreach ($words as $word)::><: $word :> <: endforeach :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerVar('words', array('YES', 'NO', 'MAYBE'));
        $goodLooking->display();
    }
    
    
    /**
     * @depends testMultipleStatementsInBlock
     */
    public function testEmptyStatement()
    {
        $this->expectOutputString('');
        
        file_put_contents($this->template, '<: ;;; :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /**
     * @depends testOutputStringLiteral
     */
    public function testArrayAccessStringKey()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: $arr["bla"] :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerVar('arr', array('bla' => 'YES'));
        $goodLooking->display();
    }
    
    /**
     * @depends testOutputStringLiteral
     */
    public function testArrayAccessIntKey()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: $arr[0] :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerVar('arr', array('YES'));
        $goodLooking->display();
    }
    
    /**
     * @depends testOutputStringLiteral
     */
    public function testArrayAccessStringVariableKey()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: $arr[$key] :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
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
        
        file_put_contents($this->template, '<: $arr[$key] :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerVar('arr', array('YES',
                                               'NO'));
        $goodLooking->registerVar('key', 0);
        $goodLooking->display();
    }
    
    public function testUsingCache()
    {
        // Note: sometimes misbehaves on my local setup that has the file being
        //       accessed over a sambe mounted file system (on a virtual network)
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, 'NO');
        // this cached value is newer than the template, so it should be
        // what is served
        file_put_contents($this->template . '.compiledTemplate', 'YES');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    public function testNotOverusingCache()
    {
        // Note: sometimes misbehaves on my local setup that has the file being
        //       accessed over a sambe mounted file system (on a virtual network)
        $this->expectOutputString('NO');
        
        file_put_contents($this->template . '.compiledTemplate', 'YES');
        
        // We need to wait a second before this actually workds (because filemtime
        // only different after a second
        // This is no problem, though, as this is something that should happen manually
        // and thus never more than once per second.
        sleep(1);
        
        file_put_contents($this->template, 'NO');
        
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    
    /**
     * @depends testOutputStringVariable
     */
    public function testRegisterMultipleVars()
    {
        $this->expectOutputString('YESNO');
        
        file_put_contents($this->template, '<: $a :><: $b :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
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
        
        file_put_contents($this->template, '<: 1 + 1 :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    
    /**
     * @depends testOutputIntLiteral
     */
    public function testSubtractionOperator()
    {
        $this->expectOutputString('0');
        
        file_put_contents($this->template, '<: 1 - 1 :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    
    /**
     * @depends testOutputIntLiteral
     */
    public function testDivisionOperator()
    {
        $this->expectOutputString('2');
        
        file_put_contents($this->template, '<: 4 / 2 :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    
    /**
     * @depends testOutputIntLiteral
     */
    public function testMultiplicationOperator()
    {
        $this->expectOutputString('6');
        
        file_put_contents($this->template, '<: 2 * 3 :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    
    /**
     * @depends testOutputStringLiteral
     */
    public function testConcatenationOperator()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: "Y" . "E" . "S" :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /**
     * @depends testIfTrueLiteral
     * @depends testDoubleIfOnOneLine
     */
    public function testEqualityOperator()
    {
        $this->expectOutputString('YES-YES');
        
        file_put_contents($this->template, '<: if ("a" == "a"): :>YES<: endif :>' .
                                           '<: if ("a" == "b"): :>NO<: endif :>' . 
                                           '<: if ("1" == 1): :>-YES<: endif :>' .
                                           '<: if ("1" == 2): :>-NO<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /**
     * @depends testIfTrueLiteral
     * @depends testDoubleIfOnOneLine
     */
    public function testOtherEqualityOperator()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if ("a" === "a"): :>YES<: endif :>' .
                                           '<: if ("a" === "b"): :>NO<: endif :>' . 
                                           '<: if ("1" === 1): :>-YES<: endif :>' .
                                           '<: if ("1" === 2): :>-NO<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /**
     * @depends testIfTrueLiteral
     * @depends testDoubleIfOnOneLine
     */
    public function testInequalityOperator()
    {
        $this->expectOutputString('NO-NO');
        
        file_put_contents($this->template, '<: if ("a" != "a"): :>YES<: endif :>' .
                                           '<: if ("a" != "b"): :>NO<: endif :>' . 
                                           '<: if ("1" != 1): :>-YES<: endif :>' .
                                           '<: if ("1" != 2): :>-NO<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /**
     * @depends testIfTrueLiteral
     * @depends testDoubleIfOnOneLine
     */
    public function testOtherInequalityOperator()
    {
        $this->expectOutputString('NO-YES-NO');
        
        file_put_contents($this->template, '<: if ("a" !== "a"): :>YES<: endif :>' .
                                           '<: if ("a" !== "b"): :>NO<: endif :>' . 
                                           '<: if ("1" !== 1): :>-YES<: endif :>' .
                                           '<: if ("1" !== 2): :>-NO<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /**
     * @depends testIfTrueLiteral
     * @depends testDoubleIfOnOneLine
     */
    public function testModulusOperator()
    {
        $this->expectOutputString('2');
        
        file_put_contents($this->template, '<: 42 % 5 :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /**
     * @depends testIfTrueLiteral
     * @depends testDoubleIfOnOneLine
     */
    public function testLargerThanOperator()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if (4 > 3): :>YES<: endif :>' .
                                      '<: if (3 > 3): :>MAYBE<: endif :>' .
                                      '<: if (2 > 3): :>NO<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /**
     * @depends testIfTrueLiteral
     * @depends testDoubleIfOnOneLine
     */
    public function testLessThanOperator()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if (2 < 3): :>YES<: endif :>' .
                                      '<: if (3 < 3): :>MAYBE<: endif :>' .
                                      '<: if (4 < 3): :>NO<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /**
     * @depends testIfTrueLiteral
     * @depends testDoubleIfOnOneLine
     */
    public function testLessThanOrEqualsOperator()
    {
        $this->expectOutputString('YESMAYBE');
        
        file_put_contents($this->template, '<: if (4 >= 3): :>YES<: endif :>' .
                                      '<: if (3 >= 3): :>MAYBE<: endif :>' .
                                      '<: if (2 >= 3): :>NO<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /**
     * @depends testIfTrueLiteral
     * @depends testDoubleIfOnOneLine
     */
    public function testLargerThanOrEqualsOperator()
    {
        $this->expectOutputString('YESMAYBE');
        
        file_put_contents($this->template, '<: if (2 <= 3): :>YES<: endif :>' .
                                      '<: if (3 <= 3): :>MAYBE<: endif :>' .
                                      '<: if (4 <= 3): :>NO<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
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
        
        file_put_contents($this->template, '<: 2 + 2 * 2 / 4 - 1 :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
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
        
        file_put_contents($this->template, '<: (2 + 2) * 2 / 4 - 1 :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfTrueLiteral
     */
    public function testAndTT()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if (true && true): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testAndTF()
    {
        $this->expectOutputString('');
        
        file_put_contents($this->template, '<: if (true && false): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testAndFT()
    {
        $this->expectOutputString('');
        
        file_put_contents($this->template, '<: if (false && true): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfFalseLiteral
     */
    public function testAndFF()
    {
        $this->expectOutputString('');
        
        file_put_contents($this->template, '<: if (false && false): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfTrueLiteral
     */
    public function testAlternateAndTT()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if (true and true): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testAlternateAndTF()
    {
        $this->expectOutputString('');
        
        file_put_contents($this->template, '<: if (true and false): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testAlternateAndFT()
    {
        $this->expectOutputString('');
        
        file_put_contents($this->template, '<: if (false and true): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfFalseLiteral
     */
    public function testAlternateAndFF()
    {
        $this->expectOutputString('');
        
        file_put_contents($this->template, '<: if (false and false): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfTrueLiteral
     */
    public function testOrTT()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if (true || true): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testOrTF()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if (true || false): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testOrFT()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if (false || true): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfFalseLiteral
     */
    public function testOrFF()
    {
        $this->expectOutputString('');
        
        file_put_contents($this->template, '<: if (false || false): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfTrueLiteral
     */
    public function testAlternateOrTT()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if (true or true): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testAlternateOrTF()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if (true or false): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testAlternateOrFT()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if (false or true): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfFalseLiteral
     */
    public function testAlternateOrFF()
    {
        $this->expectOutputString('');
        
        file_put_contents($this->template, '<: if (false or false): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfTrueLiteral
     * @depends testifFalseLiteral
     */
    public function testXorTT()
    {
        $this->expectOutputString('');
        
        file_put_contents($this->template, '<: if (true xor true): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testXorTF()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if (true xor false): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfTrueLiteral
     * @depends testIfFalseLiteral
     */
    public function testXorFT()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if (false xor true): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfFalseLiteral
     */
    public function testXorFF()
    {
        $this->expectOutputString('');
        
        file_put_contents($this->template, '<: if (false xor false): :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testTextOnly
     */
     public function testComments()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, 'Y<:- comment 1 -:>E<:- comment 2-:>S');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testOutputStringLiteral
     */
    public function testCommentsInCode()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: "Y<:- comment 1 -:>E<:- comment 2-:>S" :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
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
        
        file_put_contents($this->template, '<: IF (tRuE)    :;;; "A"; endIF :>' . 
                                            '<: fOrRange (1 --> 2)
                                            ::>B<:
                                            Endforrange
                                            :>' .
                                            '<: ForeacH($bla As $b)::>C<:eNdFoReAcH:>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        
        $goodLooking->registerVar('bla', array(0, 1));
        
        $goodLooking->display();
    }
    
    /**
     * @depends testOutputStringVariable
     */
    public function testPropertyAccess()
    {
        $this->expectOutputString('bla');
        
        file_put_contents($this->template, '<: $var->prop :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
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
        
        file_put_contents($this->template, '<: $var ->prop :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
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
        
        file_put_contents($this->template, '<: $var-> prop :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
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
        
        file_put_contents($this->template, '<: $var -> prop :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
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
        
        file_put_contents($this->template, '<: $var[0]->prop[0]->prop :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
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
        
        file_put_contents($this->template, '<: if (false): :>A<: elseif (false): :>B<: else: :>C<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    public function testElseifFT()
    {
        $this->expectOutputString('B');
        
        file_put_contents($this->template, '<: if (false): :>A<: elseif (true): :>B<: else: :>C<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    public function testElseifTF()
    {
        $this->expectOutputString('A');
        
        file_put_contents($this->template, '<: if (true): :>A<: elseif (false): :>B<: else: :>C<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    public function testElseifTT()
    {
        $this->expectOutputString('A');
        
        file_put_contents($this->template, '<: if (true): :>A<: elseif (true): :>B<: else: :>C<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    public function testElseifMany()
    {
        $this->expectOutputString('G');
        
        file_put_contents($this->template, '<: if (false): :>A' . 
                                           '<: elseif (false): :>B' . 
                                           '<: elseif (false): :>C' . 
                                           '<: elseif (false): :>D' . 
                                           '<: elseif (false): :>E' . 
                                           '<: elseif (false): :>F' . 
                                           '<: elseif (true): :>G' . 
                                           '<: elseif (false): :>H' . 
                                           '<: elseif (true): :>I' . 
                                           '<: else: :>J<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    public function testElseifWithoutElse()
    {
        $this->expectOutputString('C');
        
        file_put_contents($this->template, '<: if (false): :>A' . 
                                           '<: elseif (false): :>B' . 
                                           '<: elseif (true): :>C' . 
                                           '<: elseif (false): :>D<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    public function testInlineControlStructures()
    {
        // control structures without dropping out of code mode, no semicolons
        
        $this->expectOutputString('AAA');
        
        file_put_contents($this->template, '<: forrange(1 --> 3): if (true): "A"; endif; endforrange; :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    public function testForrangeAs()
    {
        $this->expectOutputString('456');
        
        file_put_contents($this->template, '<: forrange(4 --> 6 as $i): $i; endforrange; :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    public function testForrangeAsPostLoopZeroBased()
    {
        $this->expectOutputString('7');
        
        file_put_contents($this->template, '<: forrange(0 --> 6 as $i): endforrange; $i; :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    public function testForrangeAsPostLoopNonZeroBased()
    {
        $this->expectOutputString('7');
        
        file_put_contents($this->template, '<: forrange(4 --> 6 as $i): endforrange; $i :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testOutputIntVariable
     */
    public function testVariableAssignment()
    {
        $this->expectOutputString('6');
        
        file_put_contents($this->template, '<: $a = 6; $a :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testOutputIntVariable
     */
    public function testMultipleVariableAssignment()
    {
        $this->expectOutputString('666');
        
        file_put_contents($this->template, '<: $a = $b = $c = 6; $a; $b; $c :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testOutputIntVariable
     */
    public function testMultipleSeperateVariableAssignment()
    {
        $this->expectOutputString('5610');
        
        file_put_contents($this->template, '<: $a = 5; $b = 6; $c = $a + 5; $a; $b; $c :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testForrangeAs
     */
    public function testForrangeReuse()
    {
        $this->expectOutputString('12a1a2');
        
        file_put_contents($this->template, '<: forrange (1 --> 2 as $i): $i; endforrange; forrange(1-->2 as $i): "a"; $i; endforrange :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testForreach
     * @depends testArrayAccessIntKey
     */
    public function testForeachReuse()
    {
        $this->expectOutputString('abqaqb');
        
        file_put_contents($this->template, '<: foreach ($arr as $a): $a; endforeach; foreach($arr as $a): "q"; $a; endforeach :>');
        
        
        $goodLooking = new \Good\Looking\Looking($this->template);
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
        
        file_put_contents($this->template, '<: foreach ($arr as $a): $a; endforeach; forrange(1-->2 as $a): "q"; $a; endforrange :>');
        
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerVar('arr', array('a', 'b'));
        $goodLooking->display();
    }
    
    public function testCallFunction()
    {
        require 'DummyFunctionHandler1.php';
    
        $this->expectOutputString('ABBA');
        
        file_put_contents($this->template, '<: a(); b(); b(); a() :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerFunctionHandler('DummyFunctionHandler1');
        $goodLooking->display();
    }
    
    public function testCallChainedFunction()
    {
        require 'DummyFunctionHandler2.php';
    
        $this->expectOutputString('6');
        
        file_put_contents($this->template, '<: inc(inc(inc(3))) :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerFunctionHandler('DummyFunctionHandler2');
        $goodLooking->display();
    }
    
    public function testCallFunctionsFromTwoHandlers()
    {
        require 'DummyFunctionHandler3.php';
        require 'DummyFunctionHandler4.php';
    
        $this->expectOutputString('Handler3Handler4');
        
        file_put_contents($this->template, '<: a(); b(); :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerFunctionHandler('DummyFunctionHandler3');
        $goodLooking->registerFunctionHandler('DummyFunctionHandler4');
        $goodLooking->display();
    }
    
    public function testCallFunctionMultipleArguments()
    {
        require 'DummyFunctionHandler5.php';
    
        $this->expectOutputString('6');
        
        file_put_contents($this->template, '<: add(2, 4) :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerFunctionHandler('DummyFunctionHandler5');
        $goodLooking->display();
    }
    
    public function testCallFunctionsThatShareState()
    {
        require 'DummyFunctionHandler6.php';
    
        $this->expectOutputString('345');
        
        file_put_contents($this->template, '<: set(4); set(3); get(); set(4); get(); set(99); set(5); get() :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerFunctionHandler('DummyFunctionHandler6');
        $goodLooking->display();
    }
    
    public function testAutoEscape()
    {
        $this->expectOutputString('&lt;br&gt;');
        
        file_put_contents($this->template, '<: "<br>" :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
}

?>