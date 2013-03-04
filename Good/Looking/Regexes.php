<?php

//namespace \Good\Looking
//{

    // function str_replace_once, modeled to behave like str_replace, 
    // even where not necessary
    // however, because it is all done recursive, it does go into 
    // depth of an array, unlike str_replace
    function str_replace_once($search, $replace, $subject)
    {
        if (is_array($subject))
        {
            foreach ($subject as &$newSubject)
            {
                $newSubject = str_replace($search, $replace, $newSubject);
            }
            
            return $subject;
        }
        
        if (is_array($search))
        {
            foreach ($search as $newSearch)
            {
                if (is_array($replace))
                {
                    list($key, $newReplace) = each($replace);
                    
                    if ($key == null && $newReplace == null)
                    {
                        $newReplace = '';
                    }
                }
                else
                {
                    $newReplace = $replace;
                }
                
                $subject = str_replace_once($newSearch, $newReplace, $subject);
            }
            
            if (is_array($replace))
            {
                reset($replace);
            }
            
            return $subject;
        }
        
        return substr($subject, 0, strpos($subject, $search)) . 
                $replace . 
                 substr($subject, strpos($subject, $search) + strlen($search));
    }

    Class GoodLookingRegexes
    {
        // independent regexes
        static public $varName;
        static public $controlStructure;
        static public $scriptDelimiterLeft;
        static public $scriptDelimiterRight;
        static public $statementEnder;
        static public $commentDelimiterLeft;
        static public $commentDelimiterRight;
        static public $stringSingle;
        static public $stringDouble;
        static public $literalNumber;
        static public $literalBoolean;
        static public $operator;
        
        static public $allControlStructures;
        static public $startingControlStructures;
        static public $branchingControlStructures;
        static public $endingControlStructures;
        
        // concatenated regexes
        static public $script;
        static public $comment;
        static public $literalString;
        
        static public $controlStructureConditions = array(); // holds one regex per CS that has a condition
        
        // monkey dancers
        static public $variable;
        static public $func;
        static public $term;
        static public $expression;
    }
    
    
    /*-------------------------------------------------------*/
    /*                DEFINING THE REGEXES                   */
    /*-------------------------------------------------------*/
    
    // regexes not relying on any others
    
	// An ugly hack to avoid matching else
	// but this should be removed when we use a lexer like a good kid
	// (possibly even earlier)
    GoodLookingRegexes::$varName = '\\b(?!else)[A-Za-z][A-Za-z0-9_]*\\b';
	
    GoodLookingRegexes::$controlStructure = '\\b(?:(?:(?:end )?(if|for|foreach))|else)\\b';
    GoodLookingRegexes::$scriptDelimiterLeft = '<:';
    GoodLookingRegexes::$scriptDelimiterRight = ':>';
    GoodLookingRegexes::$statementEnder = ';';
    GoodLookingRegexes::$commentDelimiterLeft = '<:-';
    GoodLookingRegexes::$commentDelimiterRight = '-:>';
    GoodLookingRegexes::$stringSingle = "'(?:[^\\\\']|\\\\'|\\\\)*(?<!\\\\)'";
    GoodLookingRegexes::$stringDouble = '"(?:[^\\\\"]|\\\\"|\\\\)*(?<!\\\\)"';
    GoodLookingRegexes::$literalNumber = '\\b[0-9]+\\b';
    GoodLookingRegexes::$literalBoolean = '(?P<boolean>true|false)';
    GoodLookingRegexes::$operator = '(?P<operator>\+|-|\/|\*|\|\||\bor\b|&&|\band\b|==|=|!=|>=|<=|>|<|\.)';
    
    GoodLookingRegexes::$allControlStructures = '(?P<structure>(?:(?:end\s*)?(?:if|for|foreach))|else)(?:\s*\((?P<condition>.*)\))?';
    GoodLookingRegexes::$startingControlStructures = '(?P<structure>if|for|foreach)\s*\((?P<condition>.*)\)';
    GoodLookingRegexes::$branchingControlStructures = '(?P<structure>else)';
    GoodLookingRegexes::$endingControlStructures = '^\s*(?P<structure>end\s*(?:if|for|foreach))\s*$';
    
    // for for-regex \\term1 should contain first term, \\term2 the last term
    GoodLookingRegexes::$controlStructureConditions['for'] = '^(?P<term1>[\s\S]*)-->(?P<term2>[\s\S]*)$';
    // for foreach-regex \\varName should contain variable name, \\array the array
    GoodLookingRegexes::$controlStructureConditions['foreach'] = '^\s*(?P<varName>' . GoodLookingRegexes::$varName . ')\s+in\s(?P<array>[\s\S]*)$';
    
    
    // regexes that use others for concatenation
    
    GoodLookingRegexes::$script = GoodLookingRegexes::$scriptDelimiterLeft . '[\s\S]*?' . GoodLookingRegexes::$scriptDelimiterRight;
    GoodLookingRegexes::$comment = GoodLookingRegexes::$commentDelimiterLeft . '[\s\S]*?' . 
                                            GoodLookingRegexes::$commentDelimiterRight;
    GoodLookingRegexes::$literalString = '(?:' . GoodLookingRegexes::$stringDouble . ')|(?:' . 
                            GoodLookingRegexes::$stringSingle . ')';
    
    // regexes that are going to do the monkey dance (preventing double definitions in
    // circular references). Here they are stored in their pre-monkey dance variables
    
    $pre_variable = '(?P<variable>(?P<varName>' . GoodLookingRegexes::$varName . 
                                        ')(?P<arrayItemSelector>(?:\[(?P>expression)\])*))';
    $pre_func = '(?P<function>\b[a-zA-Z][a-zA-Z0-9_]*\((?P<arguments>(?P>expression)(?:,(?P>expression))*)?\))';
    
    $pre_term = '(?P<term>\s*(?:(?:' . GoodLookingRegexes::$literalNumber . 
                         ')|(?P>function)|' . GoodLookingRegexes::$literalBoolean .
                          '|(?P>variable)|(?:' . GoodLookingRegexes::$literalString . 
                           ')|\((?P>expression)\)))';
    
    $pre_expression = '(?P<expression>(?P>term)\s*(?:' . 
                                        GoodLookingRegexes::$operator .
                                            '\s*(?P>term)\s*)*)';
    
    // DA MONKEY DANCE!!
    
    GoodLookingRegexes::$variable = str_replace_once(array('(?P>expression)', '(?P>term)', '(?P>function)'),
                                        array($pre_expression,   $pre_term,   $pre_func),
                                        $pre_variable);
    
    GoodLookingRegexes::$func = str_replace_once(array('(?P>expression)', '(?P>term)', '(?P>variable)'),
                                    array($pre_expression,   $pre_term,   $pre_variable),
                                    $pre_func);
    
    GoodLookingRegexes::$term = str_replace_once(array('(?P>function)', '(?P>expression)', '(?P>variable)'),
                                    array($pre_func,       $pre_expression,   $pre_variable),
                                    $pre_term);
    
    GoodLookingRegexes::$expression = str_replace_once(array('(?P>term)', '(?P>function)', '(?P>variable)'),
                                          array($pre_term,   $pre_func,       $pre_variable),
                                          $pre_expression);
//} // \Good\Looking