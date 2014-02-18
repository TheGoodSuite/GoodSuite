<?php

class GoodLookingTest extends PHPUnit_Framework_TestCase
{
    private $template = '';
    
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
        
        file_put_contents($this->template, '<: if (true) :>YES<: end if :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    public function testIfFalseLiteral()
    {
        $this->expectOutputString('');
        
        file_put_contents($this->template, '<: if (false) :>YES<: end if :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    public function testIfTrueVariable()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if ($var) :>YES<: end if :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerVar('var', true);
        $goodLooking->display();
    }
    
    public function testIfFalseVariable()
    {
        $this->expectOutputString('');
        
        file_put_contents($this->template, '<: if ($var) :>YES<: end if :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerVar('var', false);
        $goodLooking->display();
    }
    
    public function testIfElseTrue()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if ($var) :>YES<: else:>NO<: end if :>');
        
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
        
        file_put_contents($this->template, '<: if (true) :>YES<: endif:><: if (false):>NO<: end if :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerVar('var', true);
        $goodLooking->display();
    }
    
    public function testIfElseFalse()
    {
        $this->expectOutputString('NO');
        
        file_put_contents($this->template, '<: if ($var) :>YES<: else :>NO<: end if :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerVar('var', false);
        $goodLooking->display();
    }
    
    public function testForUpwards()
    {
        $this->expectOutputString('YES YES YES YES YES ');
        
        file_put_contents($this->template, '<: for ($a --> $b) :>YES <: end for :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerVar('a', 1);
        $goodLooking->registerVar('b', 5);
        $goodLooking->display();
    }
    
    public function testForDownwards()
    {
        $this->expectOutputString('YES YES YES YES YES ');
        
        file_put_contents($this->template, '<: for ($a --> $b) :>YES <: end for :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->registerVar('a', 5);
        $goodLooking->registerVar('b', 1);
        $goodLooking->display();
    }
    
    public function testForeach()
    {
        $this->expectOutputString('YES NO MAYBE ');
        
        file_put_contents($this->template, '<: foreach ($words as $word):><: $word :> <: end foreach :>');
        
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
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if ("a" == "a") :>YES<: end if :>' .
                                           '<: if ("a" == "b") :>NO<: end if :>');
        
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
        
        file_put_contents($this->template, '<: if ("a" = "a") :>YES<: end if :>' .
                                      '<: if ("a" = "b") :>NO<: end if :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /**
     * @depends testIfTrueLiteral
     * @depends testDoubleIfOnOneLine
     */
    public function testInequalityOperator()
    {
        $this->expectOutputString('NO');
        
        file_put_contents($this->template, '<: if ("a" != "a") :>YES<: end if :>' .
                                      '<: if ("a" != "b") :>NO<: end if :>');
        
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
        
        file_put_contents($this->template, '<: if (4 > 3) :>YES<: end if :>' .
                                      '<: if (3 > 3) :>MAYBE<: end if :>' .
                                      '<: if (2 > 3) :>NO<: end if :>');
        
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
        
        file_put_contents($this->template, '<: if (2 < 3) :>YES<: end if :>' .
                                      '<: if (3 < 3) :>MAYBE<: end if :>' .
                                      '<: if (4 < 3) :>NO<: end if :>');
        
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
        
        file_put_contents($this->template, '<: if (4 >= 3) :>YES<: end if :>' .
                                      '<: if (3 >= 3) :>MAYBE<: end if :>' .
                                      '<: if (2 >= 3) :>NO<: end if :>');
        
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
        
        file_put_contents($this->template, '<: if (2 <= 3) :>YES<: end if :>' .
                                      '<: if (3 <= 3) :>MAYBE<: end if :>' .
                                      '<: if (4 <= 3) :>NO<: end if :>');
        
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
        
        file_put_contents($this->template, '<: if (true && true) :>YES<: endif :>');
        
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
        
        file_put_contents($this->template, '<: if (true && false) :>YES<: end if :>');
        
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
        
        file_put_contents($this->template, '<: if (false && true) :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfFalseLiteral
     */
    public function testAndFF()
    {
        $this->expectOutputString('');
        
        file_put_contents($this->template, '<: if (false && false) :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfTrueLiteral
     */
    public function testAlternateAndTT()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if (true and true) :>YES<: endif :>');
        
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
        
        file_put_contents($this->template, '<: if (true and false) :>YES<: endif :>');
        
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
        
        file_put_contents($this->template, '<: if (false and true) :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfFalseLiteral
     */
    public function testAlternateAndFF()
    {
        $this->expectOutputString('');
        
        file_put_contents($this->template, '<: if (false and false) :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfTrueLiteral
     */
    public function testOrTT()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if (true || true) :>YES<: endif :>');
        
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
        
        file_put_contents($this->template, '<: if (true || false) :>YES<: endif :>');
        
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
        
        file_put_contents($this->template, '<: if (false || true) :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfFalseLiteral
     */
    public function testOrFF()
    {
        $this->expectOutputString('');
        
        file_put_contents($this->template, '<: if (false || false) :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfTrueLiteral
     */
    public function testAlternateOrTT()
    {
        $this->expectOutputString('YES');
        
        file_put_contents($this->template, '<: if (true or true) :>YES<: endif :>');
        
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
        
        file_put_contents($this->template, '<: if (true or false) :>YES<: endif :>');
        
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
        
        file_put_contents($this->template, '<: if (false or true) :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfFalseLiteral
     */
    public function testAlternateOrFF()
    {
        $this->expectOutputString('');
        
        file_put_contents($this->template, '<: if (false or false) :>YES<: endif :>');
        
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
        
        file_put_contents($this->template, '<: if (true xor true) :>YES<: endif :>');
        
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
        
        file_put_contents($this->template, '<: if (true xor false) :>YES<: endif :>');
        
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
        
        file_put_contents($this->template, '<: if (false xor true) :>YES<: endif :>');
        
        $goodLooking = new \Good\Looking\Looking($this->template);
        $goodLooking->display();
    }
    
    /*
     * @depends testIfFalseLiteral
     */
    public function testXorFF()
    {
        $this->expectOutputString('');
        
        file_put_contents($this->template, '<: if (false xor false) :>YES<: endif :>');
        
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
}

?>