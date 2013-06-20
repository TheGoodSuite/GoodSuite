<?php

namespace Good\Looking;

    // function str_replace_once, modeled to behave like str_replace, 
    // even where not necessary
    // however, because it is all done recursive, it does go into 
    // depth of an array, unlike str_replace
function str_replace_once($search, $replace, $subject)
{
	if (\is_array($subject))
	{
		foreach ($subject as &$newSubject)
		{
			$newSubject = \str_replace($search, $replace, $newSubject);
		}
		
		return $subject;
	}
	
	if (\is_array($search))
	{
		foreach ($search as $newSearch)
		{
			if (\is_array($replace))
			{
				list($key, $newReplace) = \each($replace);
				
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

Class Regexes
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

// An ugly hack to avoid matching a couple of reserved words
// but this should be removed when we use a lexer like a good kid
// (possibly even earlier)
Regexes::$varName = '\\b(?!(else|end\\s*if|end\\s*for|end\\s*foreach)\\b)[A-Za-z][A-Za-z0-9_]*\\b';

Regexes::$controlStructure = '\\b(?:(?:(?:end )?(if|for|foreach))|else)\\b';
Regexes::$scriptDelimiterLeft = '<:';
Regexes::$scriptDelimiterRight = ':>';
Regexes::$statementEnder = ';';
Regexes::$commentDelimiterLeft = '<:-';
Regexes::$commentDelimiterRight = '-:>';
Regexes::$stringSingle = "'(?:[^\\\\']|\\\\'|\\\\)*(?<!\\\\)'";
Regexes::$stringDouble = '"(?:[^\\\\"]|\\\\"|\\\\)*(?<!\\\\)"';
Regexes::$literalNumber = '\\b[0-9]+\\b';
Regexes::$literalBoolean = '(?P<boolean>true|false)';
Regexes::$operator = '(?P<operator>\\+|-|\\/|\\*|\\|\\||\\bor\\b|\\bxor\\b|&&|\\band\\b|==|=|!=|>=|<=|>|<|\.)';

Regexes::$allControlStructures = '(?P<structure>(?:(?:end\\s*)?(?:if|for|foreach))|else)(?:\\s*\((?P<condition>.*)\))?';
Regexes::$startingControlStructures = '(?P<structure>if|for|foreach)\\s*\((?P<condition>.*)\)';
Regexes::$branchingControlStructures = '(?P<structure>else)';
Regexes::$endingControlStructures = '^\\s*(?P<structure>end\\s*(?:if|for|foreach))\\s*$';

// for for-regex \\term1 should contain first term, \\term2 the last term
Regexes::$controlStructureConditions['for'] = '^(?P<term1>[\\s\\S]*)-->(?P<term2>[\\s\\S]*)$';
// for foreach-regex \\varName should contain variable name, \\array the array
Regexes::$controlStructureConditions['foreach'] = '^\\s*(?P<varName>' . Regexes::$varName . ')\\s+in\\s(?P<array>[\\s\\S]*)$';


// regexes that use others for concatenation

Regexes::$script = Regexes::$scriptDelimiterLeft . '[\\s\\S]*?' . Regexes::$scriptDelimiterRight;
Regexes::$comment = Regexes::$commentDelimiterLeft . '[\\s\\S]*?' . 
										Regexes::$commentDelimiterRight;
Regexes::$literalString = '(?:' . Regexes::$stringDouble . ')|(?:' . 
						Regexes::$stringSingle . ')';

// regexes that are going to do the monkey dance (preventing double definitions in
// circular references). Here they are stored in their pre-monkey dance variables

$pre_variable = '(?P<variable>(?P<varName>' . Regexes::$varName . 
									')(?P<arrayItemSelector>(?:\\[(?P>expression)\\])*))';
$pre_func = '(?P<function>\\b[a-zA-Z][a-zA-Z0-9_]*\\((?P<arguments>(?P>expression)(?:,(?P>expression))*)?\\))';

$pre_term = '(?P<term>\\s*(?:(?:' . Regexes::$literalNumber . 
					 ')|(?P>function)|' . Regexes::$literalBoolean .
					  '|(?P>variable)|(?:' . Regexes::$literalString . 
					   ')|\\((?P>expression)\\)))';

$pre_expression = '(?P<expression>(?P>term)\\s*(?:' . 
									Regexes::$operator .
										'\\s*(?P>term)\\s*)*)';

// DA MONKEY DANCE!!

Regexes::$variable = str_replace_once(array('(?P>expression)', '(?P>term)', '(?P>function)'),
									array($pre_expression,   $pre_term,   $pre_func),
									$pre_variable);

Regexes::$func = str_replace_once(array('(?P>expression)', '(?P>term)', '(?P>variable)'),
								array($pre_expression,   $pre_term,   $pre_variable),
								$pre_func);

Regexes::$term = str_replace_once(array('(?P>function)', '(?P>expression)', '(?P>variable)'),
								array($pre_func,       $pre_expression,   $pre_variable),
								$pre_term);

Regexes::$expression = str_replace_once(array('(?P>term)', '(?P>function)', '(?P>variable)'),
									  array($pre_term,   $pre_func,       $pre_variable),
									  $pre_expression);