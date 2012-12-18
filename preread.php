<?php


class Template
{
	// the filename of the template file (is set in constructor)
	var $file;
	// the variables that the calling file registers with this class
	var $registeredVars = array();
	var $specialVars = array();
	// special vars in format: $specialVars['varName'] = array (varLayer, varID)
	// varName = name in template; varLayer = which layer defines the variable; 
	// varID = name under which you can find the variable in the layer's var section;
	
	// Some Important regexes
	var $regex_varname;
	var $regex_control_structure;
	var $regex_script_delimiter_left;
	var $regex_script_delimiter_right;
	var $regex_statementender;
	var $regex_script;
	var $regex_comment_delimiter_left;
	var $regex_comment_delimiter_right;
	var $regex_comment;
	var $regex_pre_functions;
	var $regex_string_single;
	var $regex_string_double;
	var $regex_string;
	var $regex_number;
	var $regex_prioritizer;
	
	
	// layerise is a self-recurring function. In order to prevent a lot of copying its output array is a memeber variable,
	// just like its counter and its 
	var $layerise_input;
	var $layerise_output = array();
	var $layerise_counter;
	var $layerise_subcounter;
	var $layerise_layerCount;
	
	// executeLayer is recurring too... so again I want to spare the input from being copied over and over (different instances
	// of the function are less intertwined though, so I really need to save no more than the original big input)
	var $execute_layers;
	
	// emulating an ENUM with an associative array - it enums the different modes execution can be in
	var $mapModes = array('plain' => 0, 
						     'script' => 1,
						     'layer' => 2,
						     'branch' => 3);
	
	// And another enum, this one is for the different types a layer can have when layering the document
	// for as the last thing right before execution of it all
	var $layerTypes = array('toplevel' => 0,
						     'controlStructure_starting' => 1,
						     'controlStructure_branching' => 2);
	
	// This third enum enumerates errorLevel, signifying the importance of the error
	var $errorLevels = array('low' => 0,		// like an undeclared variable, invisible on page, visible in source
						      'medium' => 1,	// visible on screen
						      'high' => 2,		// visible in javascript popup and source
						      'fatal' => 3);	// script is terminated, visible in popup and on screen
	
	// A constant variable that stores some values for control structures
	var $controlStructures;
	
	function Template($fileName)
	{
		$this->file = $fileName;
		
		// defining some important regexes
		$this->regex_varname = '\b[A-Za-z][A-Za-z0-9_]*\b';
		$this->regex_control_structure = '\b(?:(?:(?:end )?(if|for|foreach))|else)\b';
		$this->regex_script_delimiter_left = '<:';
		$this->regex_script_delimiter_right = ':>';
		$this->regex_script_statementender = ';';
		$this->regex_script = $this->regex_script_delimiter_left . '[\s\S]*?' . $this->regex_script_delimiter_right;
		// Changing regex_script is not recommended. It will create a whacky situation in the script, even though
		// it will usually give the desired result.
		// e.g. if you turn [\s\S] into [^\t] - the text "<: foo \t bar :>" will first be put into it's own array slot as
		// it is enclosed by script tags, but then in the qualification round, it will be qualified as plain text and thus it
		// will be printed directly to the file. If you can, change the delimiters instead!
		$this->regex_comment_delimiter_left = '<:-';
		$this->regex_comment_delimiter_right = '-:>';
		$this->regex_comment = $this->regex_comment_delimiter_left . '[\s\S]*?' . $this->regex_comment_delimiter_right;
		//$this->regex_pre_functions = 'include\([^)]*\)';
		$this->regex_string_single = "'(?:[^\\\\']|\\\\'|\\\\)*(?<!\\\\)'";
		$this->regex_string_double = '"(?:[^\\\\"]|\\\\"|\\\\)*(?<!\\\\)"';
		$this->regex_string = '(?:' . $this->regex_string_double . ')|(?:' . 
								$this->regex_string_single . ')';
		$this->regex_boolean = '(?P<boolean>true|false)';
		$this->regex_number = '\b[0-9]+\b';
		$this->regex_variable = '(?P<variable>(?P<varName>' . $this->regex_varname . 
									')(?P<arrayMarks>(?:\[(?P>expression)\])*))';
		$this->regex_function = '(?P<function>\b[a-zA-Z][a-zA-Z0-9_]*\((?P<arguments>(1:?P>expression)(?:,(?P>expression))*)?\))';
		$this->regex_operator = '(?P<operator>\+|-|\/|\*|\|\||\bor\b|&&|\band\b|==|=|!=|>=|<=|>|<|\.)';
		
		$this->regex_term = '(?P<term>\s*(?:(?:' . $this->regex_number . 
							 ')|(?P>function)|' . $this->regex_boolean .
							  '|(?P>variable)|(?:' . $this->regex_string . 
							   ')|\((?P>expression)\)))';
		
		$this->regex_expression = '(?P<expression>(1:?P>term)\s*(?:' . 
											$this->regex_operator .
											'\s*(?P>term)\s*)*)';
		
		// fixing some circular references in the more complicated regexes (and creating some at a good time)
		// (yes, they are actualy allowed in regexes, and no, I am not taking them out - instead I am putting them in there correcltly)
		$expression_no_term = str_replace('(1:?P>term)',
										  '(?P>term)',
										  $this->regex_expression);
		//$this->regex_expression = str_replace('(1:?P>term)', $this->regex_term,
			//									$this->regex_expression);
		$function_no_expression = str_replace('(1:?P>expression)',
										  '(?P>expression)',
										  $this->regex_function);
		//--------------------------------------------------------------------------------//
		
		//$this->regex_expression = str_replace('(?P>variable)', 
			//				'(?:' . $this->regex_variable . ')', $this->regex_expression);
		
		$expression_no_function = $this->regex_expression;
		
		$term_no_ex_no_var = str_replace('(?P>function)',
										 $function_no_expression,
										 $this->regex_term);
		
		$term_no_expression = str_replace('(?P>variable)',
										  $this->regex_variable,
										  $term_no_ex_no_var);
		
		$this->regex_function = str_replace('(1:?P>expression)',
											$expression_no_function,
											$this->regex_function);
		
		$this->regex_term = str_replace('(?P>variable)',
										'(?:' . $this->regex_variable . ')',
										$this->regex_term);
		
		$expression_no_var = str_replace('(1:?P>term)', $term_no_ex_no_var,
												$this->regex_expression);
		
		$this->regex_expression = str_replace('(1:?P>term)', $term_no_expression,
												$this->regex_expression);
		
		$term_no_function = $this->regex_term;
		
		$this->regex_term = str_replace(array('(?P>function)',
											  '(1:?P>term)'),
										array($this->regex_function,
											  '(?P>term)'),
										$this->regex_term);
		
		$this->regex_function = str_replace('(1:?P>term)',
											$term_no_function,
											$this->regex_function);
		
		$this->regex_variable = str_replace('(?P>expression)',
											$expression_no_var,
											$this->regex_variable);

		
		
		//echo "function: <xmp>{$this->regex_function}</xmp> <br /> <br />\n";
		//echo "variable: <xmp>{$this->regex_variable}</xmp> <br /> <br />\n";
		//echo "term: <xmp>{$this->regex_term}</xmp> <br /> <br />\n";
		//echo "expression: <xmp>{$this->regex_expression}</xmp> <br /> <br />\n";
		//echo "string: <xmp>{$this->regex_string}</xmp> <br /> <br />\n";
		//echo "number: <xmp>{$this->regex_number}</xmp> <br /> <br />\n";
		//echo "operator: <xmp>{$this->regex_operator}</xmp> <br /> <br />\n";
		
		
		// Some constant data to be saved for control structures
		// If you want to add a control structure, this is the only place you *should* have to change
		// (though a newly made function should be made as a member function of this class)
		// in the all regexes \\structure should contain the control structure type
		// in regex_starting and regex_branching \\condition should hold the condition (if one is allowed, else it should be null)
		$this->controlStructures['regex_all'] = '^\s*(?P<structure>(?:(?:end\s*)?(?:if|for|foreach))|else)(?:\s*\((?P<condition>.*)\))?\s*$';
		$this->controlStructures['regex_starting'] = '^\s*(?P<structure>if|for|foreach)\s*\((?P<condition>.*)\)\s*$';
		$this->controlStructures['regex_branching'] = '^\s*(?P<structure>else)\s*$';
		$this->controlStructures['regex_ending'] = '^\s*(?P<structure>end\s*(?:if|for|foreach))\s*$';
		
		//controlStructures['regex_statements'] for statement specific regexes (parsing the condition)
		// for for-regex \\term1 should contain first term, \\term2 the last term
		$this->controlStructures['regex_condition']['for'] = '^(?P<term1>[\s\S]*)-->(?P<term2>[\s\S]*)$';
		// for foreach-regex \\varName should contain variable name, \\array the array
		$this->controlStructures['regex_condition']['foreach'] = 
							'^\s*(?P<varName>' . $this->regex_varname . ')\s+in\s(?P<array>[\s\S]*)$';
		
		// branching relations: controlStructures['branchingRelations'][BRANCH STATEMENT] = array(
		// BRANCH_FROM OPTIONS)
		$this->controlStructures['branchingRelations']['else'] = array('if');
		
		//ending relations: controlStructures['endingRelations'][ENDING STATEMENT] = array(
		// ENDS OPTIONS)
		$this->controlStructures['endingRelations']['end if'] = array('if', 'else');
		$this->controlStructures['endingRelations']['end for'] = array('for');
		$this->controlStructures['endingRelations']['end foreach'] = array('foreach');
		
		// This are the functions that will be called when a control structure is found - they must be member functions
		// of <i>this</i> class
		// Should:
		// 1. Take the layer number as the first and &$result as the second argument
		// 2. Modify $result or use $result if necesary (starting something that can be branched / branching)
		// 3. Return any code that should be added to the document
		// 4. Call execute_layer some number of times (in case of an if, once if true, not at all if false)
		$this->controlStructures['functions']['if'] = 'control_structure_if';
		$this->controlStructures['functions']['else'] = 'control_structure_else';
		//$this->controlStructures['functions']['end if'] = 'control_structure_endif';
		$this->controlStructures['functions']['for'] = 'control_structure_for';
		//$this->controlStructures['functions']['end for'] = 'control_structure_endfor';
		$this->controlStructures['functions']['foreach'] = 'control_structure_foreach';
		//$this->controlStructures['functions']['end foreach'] = 'control_structure_endforeach';
	}
	
	function registerVar($varName, $varValue)
	{
		if (preg_match('/'. $this->regex_varname .'/', $varName) > 0 &&
			 preg_match('/'.$this->regex_control_structure .'/', $varName) == 0)
		// if the varName matches variable syntax and does not match a control structure
		{
			$this->registeredVars[$varName] = $varValue;
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function registerSpecialVar($varName, $varLayer, $varLayerName)
	{
		if ((preg_match('/^'. $this->regex_varname .'$/', $varName) != 0) &&
			 (preg_match('/^' . $this->regex_control_structure . '$/', $varName) == 0))
		// if the varName matches variable syntax and does not match a control structure
		{
			if ($registeredVars[$varName] != null)
			{
				$this->throwError($this->errorLevels['high'],
									"Tried to assign a special variable to an existing variable. Assigning failed.");
			}
			
			$this->specialVars[$varName] = array($varLayer, $varLayerName);
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function output()
	{
		$template = file_get_contents($this->file);
		
		$template = $this->parse($template);
		
		echo $template; //just commented out temporarily to use print_r and see the results half-way coding
		//echo "<pre>";
		//print_r($template);
		//echo "</pre>";
	}
	
	function parse($input)
	{
		//for testing I really want to know how long this function takes to be executed
		$executionTime = microtime(true);
		
		// We call splitcode which removes comments, and then makes an array of the code, and a map.
		// Plain text will be stored right in the array, while code scripts will be in sub-arrays, with a single
		// on each line. This is done in a seperate function so it can be done again for include files.
		list($splitCode, $map) = $this->splitCode($input);
		
		// Next we are going to derive a nested and fairly complex array from the split one. 
		// (and update the map accordingly)
		// We using a self-recurring function for this, so there can be an arbitrary large number of nesting levels
		$this->layerise_output = array();
		$this->layerise_input['content'] = $splitCode;
		$this->layerise_input['map'] = $map;
		$this->layerise_counter = 0;
		$this->layerise_subcounter = 0;
		$this->layerise_layerCount = 1;
		
		$this->layerise(0, $this->layerTypes['toplevel']);
		
		$this->execute_layers = $this->layerise_output;
		
		$output = $this->executeLayer(0);
		
		// for debugging
		//$output = $logModes . $output;
		
		// testing this (removing blank lines)
		//$output = str_replace(array('\r\n\r\n', '\r\r', '\n\n'), '', $output);
		
		// for testing I wanna know how long this function outputs how long it took
		echo "<div style='display: none;' name=\"time parsing took (we're doing it the regex heavy manner)\">";
		print_r((microtime(true)-$executionTime));
		echo " seconds</div> \n";
		
		return $output;
		//return $this->layerise_output;
	}
	
	function evaluate($string)
	{
		
		//  This is one hard thing I still have to implement
		
		// We go and evaluate this bit by bit. Each term will be identified as a variable, function, function with return value (I have
		// no such function planned yet, but the engine should be able to work with them), a literal value or a control
		// block signaller. Then we need to take the appropriate action.
		
		// so I reworked a lot of the core - ending up with a really good prereading system - which prereads the file
		// and takes all the comments out and sorts all the code on control structures in an array
		
		// and yet - I do still have to do this function
		// Nah. that's no longer the right way to say that
		// it oughts to be now: And right now, all I still have to do is make this function
		
		// Because really, once I have made this function, I have a early version of the system running
		
		if (preg_match('/^\s*$/', $string) != 0)
		{
			return '';
		}
		
		if (preg_match('/' . $this->regex_expression . '/', $string) == 0)
		{
			$this->throwError($this->errorLevels['fatal'], "Syntax error", $string);
		}
		
		$i = 0;
		$lastTerm = false;
		$evalString = '';
		$returnArray = null;
		
		while ($string != '')
		{
		if (preg_match('/^\s*' . $this->regex_term . 
					'\s*(?P<op>(?P>operator))?\s*/', $string, $matches) == 0)
			{
				$this->throwError($this->errorLevels['fatal'], "Syntax Error");
			}
			
			$string = preg_replace('/^\s*' . $this->regex_term . 
					'\s*(?P<op>(?P>operator))?\s*/', '', $string);
			
			$term = $matches['term'];
			$operator = $matches['op'];
			
			if (preg_match('/^\(' . $this->regex_expression . '\)$/', $term) != 0)
			{
				$evalString .= '(' . $this->evaluate($term) . ')';
			}
			else if (preg_match('/^' . $this->regex_boolean . '$/',
														$term, $matches) != 0)
			{
				$evalString .= $matches['boolean'];
			}
			else if (preg_match('/^' . $this->regex_variable . '$/', 
														$term, $matches) != 0)
			{
				if (isset($this->specialVars[$matches['varName']]))
				{
					$varLayer = $this->specialVars[$matches['varName']][0];
					$varLayerName = $this->specialVars[$matches['varName']][1];
					$var = $this->execute_layers[$varLayer]['vars'][$varLayerName];
				}
				else
				{
					$var = $this->registeredVars[$matches['varName']];
				}
				
				$arrayMarks = $matches['arrayMarks'];
				
				while ($arrayMarks != '')
				{					
					preg_match('/^\[' . $this->regex_expression . '\]/',
											$arrayMarks, $matches);
					$arrayMarks = preg_replace('/^\[' 
												. $this->regex_expression . '\]/',
												 '', $arrayMarks);
					
					$var = $var[$this->evaluate($matches['expression'])];
				}
				
				if (is_string($var))
				{
					$evalString .= '"' . $var . '"';
				}
				else if (is_numeric($var))
				{
					$evalString .= $var;
				}
				else if (is_bool($var))
				{
					if ($var)
					{
						$evalString .= 'true';
					}
					else
					{
						$evalString .= 'false';
					}
				}
				else if (is_array($var))
				{
					$returnArray = $var;
				}
			}
			else if (preg_match('/^' . $this->regex_string . '$/', $term) != 0)
			{
				$evalString .= $term;
			}
			else if (preg_match('/^' . $this->regex_number . '$/', $term) != 0)
			{
				$evalString .= $term;
			}
			else if (preg_match('/^' . $this->regex_function . '$/', $term) != 0)
			{
				// as of yet, functions are unsupported
				$this->throwError($this->errorLevels['fatal'], "Function call found while functions are currently unsupported");
			}
			else
			{
				$this->throwError($this->errorLevels['high'], "Could not qualify term as any type of term!");
			}
			
			switch ($operator)
			{
				case '+':
					$evalString .= ' + ';
					break;
					
				case '-':
					$evalString .= ' - ';
					break;
					
				case '/':
					$evalString .= ' / ';
					break;
					
				case '*':
					$evalString .= ' * ';
					break;
					
				case '||':
				case 'or':
					$evalString .= ' || ';
					break;
					
				case '&&':
				case 'and':
					$evalString .= ' && ';
					break;
					
				case '=':
				case '==':
					$evalString .= ' == ';
					break;
					
				case '!=':
					$evalString .= ' != ';
					break;
					
				case '>':
					$evalString .= ' > ';
					break;
					
				case '<':
					$evalString .= ' < ';
					break;
					
				case '>=':
					$evalString .= ' >= ';
					break;
					
				case '<=':
					$evalString .= ' <= ';
					break;
					
				case '.':
					$evalString .= ' . ';
					break;
			}
		}
		
		if ($returnArray != null && $evalString != '')
		{
			$this->throwError($this->errorLevels['fatal'], "Tried to perform operation on array.");
		}
		
		if ($returnArray != null)
		{
			return $returnArray;
		}
		else
		{
			return eval('return (' . $evalString . ');');
		}
		
	}
	
	function pre_evaluate($input)
	{
		// bogged down version of evaluate, which only works on things it can find out at compile-time
		// this is mainly used for variable names
		
		// dunno what to do with this yet so:
		return $input;
		
	}
	
	function splitCode($string)
	{
		// First of all we remove any comments
		$string = preg_replace('/'. $this->regex_comment .'/', '', $string);
		
		// Then we do a first rough split that seperates the scripts from the text, but leaves the tags in tact
		$split = preg_split('/'. '(?=' . $this->regex_script_delimiter_left . ')|(?<=' . $this->regex_script_delimiter_right .')/', $string);
		
		// Then we loop through the newly created array, determining for each element if it's a script or plain text.
		// We store this in a new array named map.
		// Also, if it is a script, we make the element's value a new array, which contains an element for each
		// statement (seperated by ';'s). In this step any delimiters are removed and empty fields are removed.
		for ($i = 0; $i < count($split); $i++)
		{			
			if (preg_match('/'. $this->regex_script .'/', $split[$i]) > 0)
			{
				$map[$i] = $this->mapModes['script'];
				
				$split[$i] = preg_split('/('. $this->regex_script_delimiter_left . ')|(' . $this->regex_script_delimiter_right . ')|(' . $this->regex_script_statementender .')/', $split[$i], -1, PREG_SPLIT_NO_EMPTY);
				
			}
			else
			{
				$map[$i] = $this->mapModes['plain'];
			}
		}
		return array($split, $map);
	}
	
	function layerise($layerNumber, $layerType, $parentLayer = null, $controlStructure = '', $condition = '')
	// This is a self-recurring function that makes a complex nested array out of a simple split array
	// the output, the input and the counter are member variables to prevent a lot of copying (which takes time)
	{
		// was just for testing
		//echo "$layerNumber;";
		
		$this->layerise_output[$layerNumber] = array('type' => $layerType,
												     'control structure' => $controlStructure,
												     'condition' => $condition,
												     'vars' => '',
												     'content' => array(),
												     'map' => array());
		
		$layerFinished = false;
		
		//$temp = count($this->layerise_input);
		
		while ($this->layerise_counter < count($this->layerise_input['content']) && !$layerFinished)
		{
			if ($this->layerise_input['map'][$this->layerise_counter] == $this->mapModes['plain'])
			{
				$this->layerise_output[$layerNumber]['content'][] = $this->layerise_input['content'][$this->layerise_counter];
				$this->layerise_output[$layerNumber]['map'][] = $this->layerise_input['map'][$this->layerise_counter];
			}
			// the following two statements are put in else if's so their regexes are executed less often 
			// - thus improving performance
			else if ($this->layerise_input['map'][$this->layerise_counter] == $this->mapModes['script'])
			{
				if (preg_match('/'. $this->controlStructures['regex_all'] .'/',	$this->layerise_input['content'][$this->layerise_counter][$this->layerise_subcounter]) > 0)
				{
					if (preg_match('/'.$this->controlStructures['regex_starting'].'/', $this->layerise_input['content'][$this->layerise_counter][$this->layerise_subcounter], $matches) > 0)
					{
						$this->layerise_output[$layerNumber]['content'][] = $this->layerise_layerCount;
						$this->layerise_output[$layerNumber]['map'][] = $this->mapModes['layer'];
						
						$this->nextField();
						$this->layerise_layerCount++;
						$this->layerise($this->layerise_layerCount - 1, 
										$this->layerTypes['controlStructure_starting'], $layerNumber, 
											$matches['structure'], $matches['condition']);
					}
					else if (preg_match('/'.$this->controlStructures['regex_branching'].'/', $this->layerise_input['content'][$this->layerise_counter][$this->layerise_subcounter], $matches) > 0)
					{
						if ($this->checkRelation('branching', $matches['structure'], $controlStructure))
						{
							$this->layerise_output[$parentLayer]['content'][] = $this->layerise_layerCount;
							$this->layerise_output[$parentLayer]['map'][] = $this->mapModes['branch'];
							
							$this->nextField();
							$this->layerise_layerCount++;
							$this->layerise($this->layerise_layerCount - 1, 
											$this->layerTypes['controlStructure_branching'], $parentLayer, 
												$matches['structure'], $matches['condition']);
							
							// When branching, this layer is done (oops, forgot at first)
							$layerFinished = true;
						}
						else
						// relation is wrong
						{
							$this->throwError($this->errorLevels['fatal'], "Control structure branching mismatch. Tried to match " . $matches[1] . " to " . $controlStructure . ".");
						}
					}
					else if (preg_match('/'.$this->controlStructures['regex_ending'].'/', $this->layerise_input['content'][$this->layerise_counter][$this->layerise_subcounter], $matches) > 0)
					{
						if ($this->checkRelation('ending', $matches['structure'], $controlStructure))
						{
							$layerFinished = true;
						}
						else
						// relation is wrong
						{
							$this->throwError($this->errorLevels['fatal'], "Control structure ending mismatch. Tried to match " . $matches[1] . " to " . $controlStructure . ".");
						}
					}
					else
					{
						$this->throwError($this->errorLevels['fatal'], "Malformed control structure.");
					}
				}
				//else if (preg_match('/'.$this->regex_pre_functions.'/', $this->layerise_input['content'][$this->layerise_counter][$this->layerise_subcounter]) > 0)
				//{
					// yet to be implemented:-
					// execute any pre_functions... (include!!!)
					
				//}
				else
				// only case left is a non-special script tag
				{
					$this->layerise_output[$layerNumber]['content'][] = $this->layerise_input['content'][$this->layerise_counter][$this->layerise_subcounter];
					$this->layerise_output[$layerNumber]['map'][] = $this->layerise_input['map'][$this->layerise_counter];
				}
			}
			
			if (!$layerFinished)
			{
				$this->nextField();
			}
		}
		
		if (!$layerFinished && $layerNumber != 0)
		{
			$this->throwError($this->errorLevels['high'], "Not all control structures were ended, before the end of the document.");
		}
	}
	
	function nextField()
	{
		if ($this->layerise_subcounter + 1 < count($this->layerise_input['content'][$this->layerise_counter]))
		{
			$this->layerise_subcounter++;
		}
		else
		{
			$this->layerise_subcounter = 0;
			$this->layerise_counter++;
		}
	}
	
	function checkRelation($relationType, $subject, $relatesWith)
	{
		$relates = false;
		
		foreach ($this->controlStructures[$relationType . 'Relations'][$subject] as $relation)
		{
			if ($relation == $relatesWith)
			{
				$relates = true;
			}
		}
		
		return $relates;
	}
	
	function throwError($errorLevel, $errorMessage)
	{
		if ($errorLevel == $this->errorLevels['fatal'])
		{
			$prefix = "Fatal Error: ";
		}
		else
		{
			$prefix = "Error: ";
		}
		
		if ($errorLevel == $this->errorLevels['low'] || $errorLevel == $this->errorLevels['high'])
		{
			echo "<div class='error' style='display: none;'>";
			echo $errorMessage;
			echo "</div>\n";
		}
		
		if ($errorLevel == $this->errorLevels['medium'] || $errorLevel == $this->errorLevels['fatal'])
		{			
			echo "<b>$prefix</b>";
			echo $errorMessage;
			echo "<br /> \n";
		}
		
		if ($errorLevel == $this->errorLevels['high'] || $errorLevel == $this->errorLevels['fatal'])
		{
			echo "<script type='text/javascript'>alert('{$prefix}{$errorMessage}')</script>";
		}
		
		if ($errorLevel == $this->errorLevels['fatal'])
		{
			// for finding a mistake in my code (debugging)
			exit;
		}
	}
	
	function plainText($string)
	{
	//	if (str_replace(array("\t", "\n", "\r", " "), '', $string) == '')
	//	{
	//		return '';	
	//	}
	//	else
	//	{
			return $string;
	//	}
	}
	
	function executeLayer($layerNumber)
	{
		// variable that is going to be passed by reference to control structure functions
		// starting control structures can modify it, whereas branching ones can read it
		$result = true;
		
		// where we are going to leave the return value
		$output = '';
		
		// work your way through each term in the layer
		foreach ($this->execute_layers[$layerNumber]['content'] as 
									$number => $content)
		// and call the appropriate function for each one (plainText, evaluate, or control structure function)
		{
			$mode = $this->execute_layers[$layerNumber]['map'][$number];
			
			if ($mode == $this->mapModes['plain'])
			{
				$output .= $this->plainText($content);
			}
			else if ($mode == $this->mapModes['script'])
			{
				$output .= $this->evaluate($content);
			}
			else if ($mode == $this->mapModes['layer'] || 
						$mode == $this->mapModes['branch'])
			{
				$control_structure = $this->execute_layers[$content]['control structure'];
				$function = $this->controlStructures['functions'][$control_structure];
				
				$output .= $this->$function($content, $result);
			}
		}
		
		return $output;
	}
	
	function control_structure_if($layerNumber, &$result)
	{
		
		$condition_evaluated = 
				$this->evaluate($this->execute_layers[$layerNumber]['condition']);
		
		if (!is_bool($condition_evaluated))
		{
			$this->throwError($this->errorLevels['high'],
							   "Non-boolean value found as condition for if-statement. False assumed.");
			$condition_evaluated = false;
		}
		
		$result = $condition_evaluated;
		
		if ($condition_evaluated)
		{
			return $this->executeLayer($layerNumber);
		}
		else
		{
			return '';
		}
	}
	
	function control_structure_else($layerNumber, &$result)
	{
		if (!$result)
		{
			return $this->executeLayer($layerNumber);
		}
		else
		{
			return '';
		}
	}
	
	function control_structure_for($layerNumber, &$result)
	{
		$result = false;
		$return_value = '';
		
		if (preg_match('/'.$this->controlStructures['regex_condition']['for'].'/', 
		     $this->execute_layers[$layerNumber]['condition'], 
			  $values) <= 0)
		{
			$this->throwError($this->errorLevels['high'],
							   "Invalid condition for for-loop. Loop was skipped.");
		}
		else
		{
			$values['term1'] = 0 + $this->evaluate($values['term1']);
			$values['term2'] = 0 + $this->evaluate($values['term2']);
			
			if ($values['term1'] <= $values['term2'])
			{
				for ($i = $values['term1']; $i <= $values['term2']; $i++)
				{
					$this->execute_layers[$layerNumber]['vars']['i'] = $i;
					
					$return_value .= $this->executeLayer($layerNumber);
					
					$result = true;
				}
			}
			else
			{
				for ($i = $values['term1']; $i >= $values['term2']; $i--)
				{
					$this->execute_layers[$layerNumber]['vars']['i'] = $i;
					
					$return_value .= $this->executeLayer($layerNumber);
					
					$result = true;
				}
			}
		}
		
		return $return_value;
	}
	
	function control_structure_foreach($layerNumber, &$result)
	{
		$result = false;
		$return_value = '';
		
		if (preg_match('/'.$this->controlStructures['regex_condition']['foreach'].'/', 
		     $this->execute_layers[$layerNumber]['condition'], 
			  $values) <= 0)
		{
			$this->throwError($this->errorLevels['high'],
							   "Invalid condition for foreach-loop. Loop was skipped.");
		}
		else
		{
			$values['varName'] = $this->pre_evaluate($values['varName']);
			$values['array'] = $this->evaluate($values['array']);
			
			$this->registerSpecialVar($values['varName'], $layerNumber, 'item');
			
			$i = 0;
			
			foreach ($values['array'] as $item)
			{
				$this->execute_layers[$layerNumber]['vars']['item'] = $item;
				$this->execute_layers[$layerNumber]['vars']['i'] = $i;
				
				$return_value .= $this->executeLayer($layerNumber);
				
				$result = true;
				$i++;
			}
		}
		
		return $return_value;
	}
}