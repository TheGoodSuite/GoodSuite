<?php

namespace Good\Looking;

    // function str_replace_once, modeled to behave like str_replace, 
    // even where not necessary
    // however, because it is all done recursive, it does go into 
    // depth of an array, unlike str_replace

Class Grammar
{
    // independent regexes
    public $keywords;
    public $scriptDelimiterLeft;
    public $scriptDelimiterRight;
    public $statementEnder;
    public $commentDelimiterLeft;
    public $commentDelimiterRight;
    public $stringSingle;
    public $stringDouble;
    public $literalInteger;
    public $literalFloat;
    public $literalBoolean;
    public $operator;
    
    public $allControlStructures;
    public $startingControlStructures;
    public $branchingControlStructures;
    public $endingControlStructures;
    
    // concatenated regexes
    public $literalNumber;
    public $varName;
    public $script;
    public $comment;
    public $literalString;
    
    // monkey dancers
    public $variable;
    public $func;
    public $term;
    public $expression;
    
    // control structures
    public $controlStructureIf;
    public $controlStructureElse;
    public $controlStructureEndIf;
    public $controlStructureFor;
    public $controlStructureEndFor;
    public $controlStructureForeach;
    public $controlStructureEndForeach;
    
    
    public function __construct()
    {
        // defining the regexes!
        
        // regexes not relying on any others
        $this->keywords = '\\b(?:end|if|for|foreach|else|endif|endfor|endforeach)\\b';
        $this->scriptDelimiterLeft = '<:';
        $this->scriptDelimiterRight = ':>';
        $this->statementEnder = ';';
        $this->commentDelimiterLeft = '<:-';
        $this->commentDelimiterRight = '-:>';
        $this->stringSingle = "'(?:[^\\\\']|\\\\'|\\\\)*(?<!\\\\)'";
        $this->stringDouble = '"(?:[^\\\\"]|\\\\"|\\\\)*(?<!\\\\)"';
        $this->literalInt = '\\b([0]+|[1-9][0-9]*)\\b';
        $this->literalFloat = '\\b[0-9]+\\.[0-9]+\\b';
        $this->literalBoolean = '(?P<boolean>true|false)';
        $this->operator = '(?P<operator>\\+|-|\\/|\\*|\\|\\||\\bor\\b|\\bxor\\b|&&|\\band\\b|==|=|!=|>=|<=|>|<|\.)';
        
        // regexes that use others through concatenation
        
        // note: float should go first, else it will think the dot is an operator
        $this->literalNumber = '(?:' . $this->literalFloat . '|' . $this->literalInt . ')';
        
        $this->varName = '\\b[A-Za-z][A-Za-z0-9_]*\\b';
        // for foreach-regex \\varName should contain variable name, \\array the array
        $this->controlStructureForeach = '^\\s*\$(?P<varName>' . $this->varName . ')\\s+in\\s(?P<array>[\\s\\S]*)$';

        $this->script = $this->scriptDelimiterLeft . '[\\s\\S]*?' . $this->scriptDelimiterRight;
        $this->comment = $this->commentDelimiterLeft . '[\\s\\S]*?' . 
                                                $this->commentDelimiterRight;
        $this->literalString = '(?:' . $this->stringDouble . ')|(?:' . 
                                $this->stringSingle . ')';
        
        // regexes that are going to do the monkey dance (preventing double definitions in
        // circular references). Here they are stored in their pre-monkey dance variables

        $preVariable = '(?P<variable>\$(?P<varName>' . $this->varName . 
                                            ')(?P<arrayItemSelector>(?:\\[(?P>expression)\\])*))';
        $preFunc = '(?P<function>\\b[a-zA-Z][a-zA-Z0-9_]*\\((?P<arguments>(?P>expression)(?:,(?P>expression))*)?\\))';

        $preTerm = '(?P<term>\\s*(?:(?:' . $this->literalNumber . 
                             ')|(?P>function)|' . $this->literalBoolean .
                              '|(?P>variable)|(?:' . $this->literalString . 
                               ')|\\((?P>expression)\\)))';

        $preExpression = '(?P<expression>(?P>term)\\s*(?:' . 
                                            $this->operator .
                                                '\\s*(?P>term)\\s*)*)';

        // DA MONKEY DANCE!!
        // note that even things as silly as the order of the search & replace array elements
        // matters a lot

        $this->variable = $this->str_replace_once(array('(?P>expression)', '(?P>term)', '(?P>function)'),
                                                  array($preExpression,    $preTerm,    $preFunc),
                                                  $preVariable);

        $this->func = $this->str_replace_once(array('(?P>expression)', '(?P>term)', '(?P>variable)'),
                                              array($preExpression,    $preTerm,    $preVariable),
                                              $preFunc);

        $this->term = $this->str_replace_once(array('(?P>function)', '(?P>expression)', '(?P>variable)'),
                                              array($preFunc,        $preExpression,   $preVariable),
                                              $preTerm);

        $this->expression = $this->str_replace_once(array('(?P>term)', '(?P>function)', '(?P>variable)'),
                                                    array($preTerm,    $preFunc,        $preVariable),
                                                    $preExpression);
        
        // and finally, we need to do some post-monkey dance concatenation
        // (in other words, these use one or more regexes from created in the
        //  monkey dance as apart of them, but are not used by them, so do
        //  not introduce any addtional circular references)
    
        $this->controlStructureIf = '(?:if\s*\((?<condition>' . $this->expression . ')\))';
        $this->controlStructureElse = 'else';
        $this->controlStructureEndIf = 'end\s*if';
        $this->controlStructureFor = '(?:for\s*\((?P<from>' . $this->expression . ')-->(?P<to>(?P>expression))\))';
        $this->controlStructureEndFor = 'end\\s*for';
        $this->controlStructureForeach = '(?:foreach\s*\(\\s*\$(?P<foreachVariable>' . $this->varName . 
                                                ')\\s+in\\s(?P<array>' . $this->expression . ')\))';
        $this->controlStructureEndForeach = 'end\\s*foreach';
    }
    
    private function str_replace_once($search, $replace, $subject)
    {
        if (\is_array($search) && \is_array($replace))
        {
            foreach ($search as $key => $newSearch)
            {
                $subject = $this->str_replace_once($newSearch, $replace[$key], $subject);
            }
            
            if (\is_array($replace))
            {
                \reset($replace);
            }
            
            return $subject;
        }
        
        return \substr($subject, 0, \strpos($subject, $search)) . 
                $replace . 
                 \substr($subject, \strpos($subject, $search) + \strlen($search));
    }
}