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
    public $propertyAccess;
    
    // concatenated regexes
    public $literalNumber;
    public $varName;
    public $script;
    public $comment;
    public $literalString;
    public $variableAssignment;
    public $statement;
    
    // monkey dancers
    public $variable;
    public $func;
    public $term;
    public $expression;
    public $arrayAccess;
    public $arrayLiteral;
    
    // control structures
    public $controlStructureIf;
    public $controlStructureElse;
    public $controlStructureEndIf;
    public $controlStructureForrange;
    public $controlStructureEndForrange;
    public $controlStructureForeach;
    public $controlStructureEndForeach;
    
    
    public function __construct()
    {
        // defining the regexes!
        
        // regexes not relying on any others
        $this->keywords = '\\b(?:(?i)if|forrange|foreach|else|elseif|endif|endforrange|endforeach)\\b';
        $this->scriptDelimiterLeft = '<:';
        $this->scriptDelimiterRight = ':>';
        $this->statementEnder = ';';
        $this->commentDelimiterLeft = '<:-';
        $this->commentDelimiterRight = '-:>';
        $this->stringSingle = "'(?:[^\\\\']|\\\\'|\\\\)*(?<!\\\\)'";
        $this->stringDouble = '"(?:[^\\\\"]|\\\\"|\\\\)*(?<!\\\\)"';
        $this->literalInt = '\\b([0]+|[1-9][0-9]*)\\b';
        $this->literalFloat = '\\b[0-9]+\\.[0-9]+\\b';
        $this->literalBoolean = '(?P<boolean>(?i)true|false)';
        // NB: if one operator is a substring of the other, the longer one should go first in the regex
        $this->operator = '(?P<operator>\\+|-|\\/|\\*|\\|\\||\\bor\\b|\\bxor\\b|&&|\\band\\b|===|==|!==|!=|>=|<=|>|<|\.|%)';
        $this->propertyAccess = '->\s*(?P<propertyName>[a-zA-Z_][0-9a-zA-Z_]*)\b';
        
        // regexes that use others through concatenation
        
        // note: float should go first, else it will think the dot is an operator
        $this->literalNumber = '(?:' . $this->literalFloat . '|' . $this->literalInt . ')';
        
        // note: dollar sign will (generally) need to be prepended to this.
        $this->varName = '\\b[A-Za-z][A-Za-z0-9_]*\\b';

        $this->script = $this->scriptDelimiterLeft . '[\\s\\S]*?' . $this->scriptDelimiterRight;
        $this->comment = $this->commentDelimiterLeft . '[\\s\\S]*?' . 
                                                $this->commentDelimiterRight;
        $this->literalString = '(?:' . $this->stringDouble . ')|(?:' . 
                                $this->stringSingle . ')';
        
        $this->variableAssignment = '\\$(?P<assignVarName>' . $this->varName . ')\\s*=\\s*';
        
        // regexes that are going to do the monkey dance (preventing double definitions in
        // circular references). Here they are stored in their pre-monkey dance variables
        
        $preArrayAccess = '(?P<arrayAccess>\[\s*(?P>expression)\s*\])';
        
        $preVariable = '(?P<variable>\$(?P<varName>' . $this->varName . 
                                            ')(?P<varModifiers>(\s*((?P>arrayAccess)|' . $this->propertyAccess . '))*))';
        $preFunc = '(?P<function>\\b(?P<functionName>[a-zA-Z][a-zA-Z0-9_]*)\\((?P<arguments>(?:(?P>expression)(?:,(?P>expression))*)?)\\))';

        $preTerm = '(?P<term>\\s*(?:(?:' . $this->literalNumber . 
                             ')|(?P>function)|' . $this->literalBoolean .
                              '|(?P>variable)|(?:' . $this->literalString . 
                               ')|(?P>arrayLiteral)|\\((?P>expression)\\)))';

        $preExpression = '(?P<expression>(?P>term)\\s*(?:' . 
                                            $this->operator .
                                                '\\s*(?P>term)\\s*)*)';

        $preArrayLiteral = '(?P<arrayLiteral>\\[(?P<arrayItems>(?P>expression)(?:,(?P>expression))*)?\\])';
        
        // DA MONKEY DANCE!!
        // note that even things as silly as the order of the search & replace array elements
        // matters a lot

        $this->variable = $this->str_replace_once(array('(?P>arrayAccess)', '(?P>expression)', '(?P>term)', '(?P>arrayLiteral)', '(?P>function)'),
                                                  array($preArrayAccess,    $preExpression,    $preTerm,    $preArrayLiteral,    $preFunc),
                                                  $preVariable);

        $this->func = $this->str_replace_once(array('(?P>expression)',  '(?P>term)', '(?P>arrayLiteral)', '(?P>variable)', '(?P>arrayAccess)'),
                                              array($preExpression,     $preTerm,    $preArrayLiteral,    $preVariable,        $preArrayAccess),
                                              $preFunc);

        $this->term = $this->str_replace_once(array('(?P>function)', '(?P>expression)', '(?P>arrayLiteral)',  '(?P>variable)', '(?P>arrayAccess)'),
                                              array($preFunc,        $preExpression,    $preArrayLiteral,     $preVariable,    $preArrayAccess),
                                              $preTerm);

        $this->expression = $this->str_replace_once(array('(?P>term)', '(?P>function)', '(?P>variable)', '(?P>arrayAccess)', '(?P>arrayLiteral)'),
                                                    array($preTerm,    $preFunc,        $preVariable,    $preArrayAccess,    $preArrayLiteral),
                                                    $preExpression);
        
        $this->arrayAccess = $this->str_replace_once(array('(?P>expression)',  '(?P>term)', '(?P>arrayLiteral)', '(?P>function)', '(?P>variable)'),
                                                     array($preExpression,     $preTerm,    $preArrayLiteral,    $preFunc,        $preVariable),
                                                     $preArrayAccess);
                                                     
        $this->arrayLiteral = $this->str_replace_once(array('(?P>expression)', '(?P>term)', '(?P>function)', '(?P>variable)', '(?P>arrayAccess)'),
                                                      array($preExpression,     $preTerm,    $preFunc,        $preVariable,    $preArrayAccess),
                                                      $preArrayLiteral);
                                                     
        
        // and finally, we need to do some post-monkey dance concatenation
        // (in other words, these use one or more regexes from created in the
        //  monkey dance as apart of them, but are not used by them, so do
        //  not introduce any addtional circular references)
    
        $this->controlStructureIf = '(?:(?i:if)\s*\((?P<condition>' . $this->expression . ')\))\s*:';
        $this->controlStructureElseif = '(?:(?i:elseif)\s*\((?P<condition>' . $this->expression . ')\))\s*:';
        $this->controlStructureElse = '(?i:else)\s*:';
        $this->controlStructureEndIf = '(?i:endif)';
        $this->controlStructureForrange = '(?:(?i:forrange)\s*\((?P<from>' . $this->expression . ')-->(?P<to>' . 
                                             '(?P>expression))(?:\b(?i:as)\s+\$(?<forrangeVariable>' . $this->varName . ')\s*)?\))\s*:';
        $this->controlStructureEndForrange = '(?i:endforrange)';
        $this->controlStructureForeach = '(?:(?i:foreach)\s*\(\\s*(?P<foreachArray>' . $this->expression . 
                                                ')\\s+(?i:as)\\s+\$(?P<foreachVariable>' . $this->varName . ')\))\s*:';
        $this->controlStructureEndForeach = '(?i:endforeach)';
        
        $this->statement = '(?P<statement>(' . $this->variableAssignment . ')*' . $this->expression . ')';
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