<?php

//namespace \Good\Looking
//{
    Class GoodLookingCompiler
    {
        // A (unofficially) constant variable that stores some values for control structures
        private $controlStructures;
        
        // Self-recurring functions that need variables (or have them so they don't create HUGE
        // overhead) will have them with the function prepended and then an underscore
        
        // These could have been static variables, but in that case it would have been real messy
        // to reset it, if after the first compiling there should be another
        
        private $layerise_output;
        private $layerise_input;
        private $layerise_counter;
        private $layerise_subCounter;
        private $layerise_layerCount;
        
        private $execute_layers;
        private $execute_templateVarCount;
        private $execute_lastOutputType;
        
        
        public function __construct()
        {
            
            // branching relations: controlStructures['branchingRelations'][BRANCH STATEMENT] = array(
            // BRANCH_FROM OPTIONS)
            $this->controlStructures['branchingRelations']['else'] = array('if');
            
            // ending relations: controlStructures['endingRelations'][ENDING STATEMENT] = array(
            // ENDS OPTIONS)
            $this->controlStructures['endingRelations']['end if'] = array('if', 'else');
            $this->controlStructures['endingRelations']['end for'] = array('for');
            $this->controlStructures['endingRelations']['end foreach'] = array('foreach');
            
            // This are the functions that will be called when a control structure is found - they must be member functions
            // of <i>this</i> class
            // Should:
            // - Take the layer number as the first
            // There's two ways of defining this 
            // (1) defining ['complete']
            // (2) defining ['top'] and/or ['bottom']
            // In case (1) function should also:
            // 2. Return any code that should be added to the document
            // In case (2) functions should also:
            // 2. Return any code that needs to be before the compiled contents for top
            // 3. Return any code that needs to be after the compiled contents for bottom
            // NOTE: if ['complete'] is specified, ['top'] and ['bottom'] are ignored
            $this->controlStructures['functions']['if']['top'] = 'controlStructure_if';
            $this->controlStructures['functions']['if']['bottom'] = 'controlStructure_closeBracket';
            $this->controlStructures['functions']['else']['top'] = 'controlStructure_else';
            $this->controlStructures['functions']['else']['bottom'] = 'controlStructure_closeBracket';
            $this->controlStructures['functions']['for']['top'] = 'controlStructure_for';
            $this->controlStructures['functions']['for']['bottom'] = 'controlStructure_closeBracket';
            $this->controlStructures['functions']['foreach']['top'] = 'controlStructure_foreach';
            $this->controlStructures['functions']['foreach']['bottom'] = 'controlStructure_closeBracket';
        } // __construct
        
        public function compile($input, $output)
        {
            $compiledTemplate = $this->compileTemplate(file_get_contents($input));
            
            $file = fopen($output, 'w+');
            fwrite($file, $compiledTemplate);
            fclose($file);
            
        } // compile
        
        private function compileTemplate($input)
        {
            
            // for testing I really want to know how long this function takes to be executed
            $executionTime = microtime(true);
            
            // We call splitcode which removes comments, and then makes an array of the code, and a map.
            // Plain text will be stored right in the array, while code scripts will be in sub-arrays, with a single
            // on each line.
            list($splitCode, $splitMap) = $this->splitCode($input);
            
            // Next we are going to derive a nested and fairly complex array from the split one. 
            // (and update the map accordingly)
            // We using a self-recurring function for this, so there can be an arbitrary large number of nesting levels
            $this->layerise_output = array();
            $this->layerise_input['content'] = $splitCode;
            $this->layerise_input['splitMap'] = $splitMap;
            $this->layerise_counter = 0;
            $this->layerise_subCounter = 0;
            $this->layerise_layerCount = 1;
            
            $this->layerise(0, GoodLookingCompilerLayerTypes::toplevel);
            
            $this->execute_layers = $this->layerise_output;
            
            $this->execute_templateVarCount = 0;
            $this->execute_lastOutputType = GoodLookingCompilerOutputTypes::text;
            
            $output = $this->executeLayer(0);
            
            // for testing I wanna know how long this function outputs how long it took
            echo '<!-- Compiling took:' . (microtime(true)-$executionTime) . ' seconds -->';
            
            return $output;
            
        } // compileTemplate
        
        
        private function evaluate($evaluateString)
        {
            
            //  w00t! finally did this function
            
            if (preg_match('/^\s*$/', $evaluateString) != 0)
            {
                return '';
            }
            
            if (preg_match('/' . GoodLookingRegexes::$expression . '/', $evaluateString) == 0)
            {
                $this->throwError(GoodLookingErrorLevels::fatal, "Syntax error", $evaluateString);
            }
            
            $output = '';
            
            while ($evaluateString != '')
            {
                if (preg_match('/^\s*' . GoodLookingRegexes::$term . 
                        '\s*(?P<op>(?P>operator))?\s*/', $evaluateString, $matches) == 0)
                {
                    $this->throwError(GoodLookingErrorLevels::fatal, "Syntax Error");
                }
                
                $evaluateString = preg_replace('/^\s*' . GoodLookingRegexes::$term . 
                        '\s*(?P<op>(?P>operator))?\s*/', '', $evaluateString);
                
                $term = $matches['term'];
                $operator = $matches['op'];
                
                if (preg_match('/^\(' . GoodLookingRegexes::$expression . '\)$/', $term) != 0)
                {
                    $output .= '(' . $this->evaluate($term) . ')';
                }
                else if (preg_match('/^' . GoodLookingRegexes::$literalBoolean . '$/',
                                                            $term, $matches) != 0)
                {
                    $output .= $matches['boolean'];
                }
                else if (preg_match('/^' . GoodLookingRegexes::$variable . '$/', 
                                                            $term, $matches) != 0)
                {
                    $templateVariable = '$this->getVar(\'' . $matches['varName'] . '\')';
                    
                    $arrayItemSelector = $matches['arrayItemSelector'];
                    
                    while ($arrayItemSelector != '')
                    {
                        preg_match('/^\[' . GoodLookingRegexes::$expression . '\]/',
                                                $arrayItemSelector, $matches);
                        $arrayItemSelector = preg_replace('/^\[' 
                                                    . GoodLookingRegexes::$expression . '\]/',
                                                     '', $arrayItemSelector);
                        
                        $templateVariable = '$this->arrayItem(' . $templateVariable . ', ' .
                                        $this->evaluate($matches['expression']) . ')';
                    }
                    
                    $output .= $templateVariable;
                }
                else if (preg_match('/^' . GoodLookingRegexes::$literalString . '$/', $term) != 0)
                {
                    $output .= $term;
                }
                else if (preg_match('/^' . GoodLookingRegexes::$literalNumber . '$/', $term) != 0)
                {
                    $output .= $term;
                }
                else if (preg_match('/^' . GoodLookingRegexes::$func . '$/', $term) != 0)
                {
                    // as of yet, functions are unsupported
                    $this->throwError(GoodLookingErrorLevels::fatal, "Function call found while functions are currently unsupported");
                }
                else
                {
                    $this->throwError(GoodLookingErrorLevels::high, "Could not qualify term as any type of term!");
                }
                
                switch ($operator)
                {
                    case '+':
                        $output .= ' + ';
                        break;
                        
                    case '-':
                        $output .= ' - ';
                        break;
                        
                    case '/':
                        $output .= ' / ';
                        break;
                        
                    case '*':
                        $output .= ' * ';
                        break;
                        
                    case '||':
                    case 'or':
                        $output .= ' || ';
                        break;
                        
                    case '&&':
                    case 'and':
                        $output .= ' && ';
                        break;
                        
                    case '=':
                    case '==':
                        $output .= ' == ';
                        break;
                        
                    case '!=':
                        $output .= ' != ';
                        break;
                        
                    case '>':
                        $output .= ' > ';
                        break;
                        
                    case '<':
                        $output .= ' < ';
                        break;
                        
                    case '>=':
                        $output .= ' >= ';
                        break;
                        
                    case '<=':
                        $output .= ' <= ';
                        break;
                        
                    case '.':
                        $output .= ' . ';
                        break;
                 
                }
            }
            
            return $output;
            
        } // evaluate
        
        private function splitCode($code)
        {
        
            // First of all we remove any comments
            $code = preg_replace('/'. GoodLookingRegexes::$comment .'/', '', $code);
            
            // Then we do a first rough split that seperates the scripts from the text, but leaves the tags in tact
            $splitCode = preg_split('/'. '(?=' . GoodLookingRegexes::$scriptDelimiterLeft . ')|(?<=' . GoodLookingRegexes::$scriptDelimiterRight .')/', $code);
            
            // Then we loop through the newly created array, determining for each element if it's a script or plain text.
            // We store this in a new array named map.
            // Also, if it is a script, we make the element's value a new array, which contains an element for each
            // statement (seperated by ';'s). In this step any delimiters are removed and empty fields are removed.
            for ($i = 0; $i < count($splitCode); $i++)
            {
                if (preg_match('/'. GoodLookingRegexes::$script .'/', $splitCode[$i]) > 0)
                {
                    $splitMap[$i] = GoodLookingCompilerMapModes::script;
                    
                    $splitCode[$i] = preg_split('/('. GoodLookingRegexes::$scriptDelimiterLeft . ')|(' . GoodLookingRegexes::$scriptDelimiterRight . ')|(' . GoodLookingRegexes::$statementEnder .')/', $splitCode[$i], -1, PREG_SPLIT_NO_EMPTY);
                    
                }
                else
                {
                    $splitMap[$i] = GoodLookingCompilerMapModes::plain;
                }
            }
            return array($splitCode, $splitMap);
            
        } // splitCode
        
        private function layerise($layerNumber, $layerType, $parentLayer = null, $controlStructure = '', $condition = '')
        // This is a self-recurring function that makes a complex nested array out of a simple split array
        // the output, the input and the counter are member variables to prevent a lot of copying (which takes time)
        {
            $this->layerise_output[$layerNumber] = array('type' => $layerType,
                                                         'controlStructure' => $controlStructure,
                                                         'condition' => $condition,
                                                         'vars' => '',
                                                         'content' => array(),
                                                         'splitMap' => array());
            
            $layerFinished = false;
            
            while ($this->layerise_counter < count($this->layerise_input['content']) && !$layerFinished)
            {
                if ($this->layerise_input['splitMap'][$this->layerise_counter] == GoodLookingCompilerMapModes::plain)
                {
                    $this->layerise_output[$layerNumber]['content'][] = $this->layerise_input['content'][$this->layerise_counter];
                    $this->layerise_output[$layerNumber]['splitMap'][] = $this->layerise_input['splitMap'][$this->layerise_counter];
                }
                // the following two statements are put in else if's so their regexes are executed less often 
                // - thus improving performance
                else if ($this->layerise_input['splitMap'][$this->layerise_counter] == GoodLookingCompilerMapModes::script)
                {
                    if (preg_match('/'. GoodLookingRegexes::$allControlStructures .'/', $this->layerise_input['content'][$this->layerise_counter][$this->layerise_subCounter]) > 0)
                    {
                        if (preg_match('/'.GoodLookingRegexes::$startingControlStructures .'/', $this->layerise_input['content'][$this->layerise_counter][$this->layerise_subCounter], $matches) > 0)
                        {
                            $this->layerise_output[$layerNumber]['content'][] = $this->layerise_layerCount;
                            $this->layerise_output[$layerNumber]['splitMap'][] = GoodLookingCompilerMapModes::layer;
                            
                            $this->layerise_nextField();
                            $this->layerise_layerCount++;
                            $this->layerise($this->layerise_layerCount - 1, 
                                            GoodLookingCompilerLayerTypes::contStrucStarting, $layerNumber, 
                                                $matches['structure'], $matches['condition']);
                        }
                        else if (preg_match('/'.GoodLookingRegexes::$branchingControlStructures .'/', $this->layerise_input['content'][$this->layerise_counter][$this->layerise_subCounter], $matches) > 0)
                        {
                            if ($this->checkRelation('branching', $matches['structure'], $controlStructure))
                            {
                                $this->layerise_output[$parentLayer]['content'][] = $this->layerise_layerCount;
                                $this->layerise_output[$parentLayer]['splitMap'][] = GoodLookingCompilerMapModes::branch;
                                
                                $this->layerise_nextField();
                                $this->layerise_layerCount++;
                                $this->layerise($this->layerise_layerCount - 1, 
                                                GoodLookingCompilerLayerTypes::contStrucBranching, $parentLayer, 
                                                    $matches['structure'], $matches['condition']);
                                
                                // When branching, this layer is done (oops, forgot at first)
                                $layerFinished = true;
                            }
                            else
                            // relation is wrong
                            {
                                $this->throwError(GoodLookingErrorLevels::fatal, "Control structure branching mismatch. Tried to match " . $matches[1] . " to " . $controlStructure . ".");
                            }
                        }
                        else if (preg_match('/'.GoodLookingRegexes::$endingControlStructures .'/', $this->layerise_input['content'][$this->layerise_counter][$this->layerise_subCounter], $matches) > 0)
                        {
                            if ($this->checkRelation('ending', $matches['structure'], $controlStructure))
                            {
                                $layerFinished = true;
                            }
                            else
                            // relation is wrong
                            {
                                $this->throwError(GoodLookingErrorLevels::fatal, "Control structure ending mismatch. Tried to match " . $matches[1] . " to " . $controlStructure . ".");
                            }
                        }
                        else
                        {
                            $this->throwError(GoodLookingErrorLevels::fatal, "Malformed control structure.");
                        }
                    }
                    else
                    // only case left is a non-special script tag
                    {
                        $this->layerise_output[$layerNumber]['content'][] = $this->layerise_input['content'][$this->layerise_counter][$this->layerise_subCounter];
                        $this->layerise_output[$layerNumber]['splitMap'][] = $this->layerise_input['splitMap'][$this->layerise_counter];
                    }
                }
                
                if (!$layerFinished)
                {
                    $this->layerise_nextField();
                }
            }
            
            if (!$layerFinished && $layerNumber != 0)
            {
                $this->throwError(GoodLookingErrorLevels::high, "Not all control structures were ended, before the end of the document.");
            }
        } // layerise
        
        private function layerise_nextField()
        {
            
            if ($this->layerise_subCounter + 1 < count($this->layerise_input['content'][$this->layerise_counter]))
            {
                $this->layerise_subCounter++;
            }
            else
            {
                $this->layerise_subCounter = 0;
                $this->layerise_counter++;
            }
        } // layerise_nextField
        
        private function checkRelation($relationType, $subject, $relatesWith)
        {
            
            $relates = false;
            
            foreach ($this->controlStructures[$relationType . 'Relations'][$subject] as $relation)
            {
                if ($relation == $relatesWith)
                {
                    $relates = true;
                    break;
                }
            }
            
            return $relates;
            
        } // checkRelation
        
        private function throwError($errorLevel, $errorMessage)
        {
            
            if ($errorLevel == GoodLookingErrorLevels::fatal)
            {
                $prefix = "Fatal Error: ";
            }
            else
            {
                $prefix = "Error: ";
            }
            
            if ($errorLevel == GoodLookingErrorLevels::low || 
                            $errorLevel == GoodLookingErrorLevels::high)
            {
                echo "<!--";
                echo $prefix . $errorMessage;
                echo "-->";
            }
            
            if ($errorLevel == GoodLookingErrorLevels::medium || 
                            $errorLevel == GoodLookingErrorLevels::fatal)
            {			
                echo "<b>$prefix</b>";
                echo $errorMessage;
                echo "<br /> \n";
            }
            
            if ($errorLevel == GoodLookingErrorLevels::high || 
                            $errorLevel == GoodLookingErrorLevels::fatal)
            {
                echo "<script type='text/javascript'>alert('{$prefix}{$errorMessage}')</script>";
            }
            
            if ($errorLevel == GoodLookingErrorLevels::fatal)
            {
                exit;
            }
        } // throwError
        
        private function plainText($string)
        { 
            return $string;
        } // plainText
        
        private function executeLayer($layerNumber)
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
                $mapMode = $this->execute_layers[$layerNumber]['splitMap'][$number];
                
                if ($mapMode == GoodLookingCompilerMapModes::plain)
                {
                    $output .= $this->execute_setCompilerOutputType(GoodLookingCompilerOutputTypes::text);
                    $output .= $this->plainText($content);
                }
                else if ($mapMode == GoodLookingCompilerMapModes::script)
                {
                    $evaluateResult = $this->evaluate($content);
                    
                    if ($evaluateResult != '')
                    {
                        $output .= $this->execute_setCompilerOutputType(GoodLookingCompilerOutputTypes::php);
                        $output .= 'echo ' . $evaluateResult .';';
                    }
                }
                else if ($mapMode == GoodLookingCompilerMapModes::layer || 
                            $mapMode == GoodLookingCompilerMapModes::branch)
                {
                    $controlStructure = $this->execute_layers[$content]['controlStructure'];
                    
                    if (isset($this->controlStructures['functions'][$controlStructure]['complete']))
                    {
                        $function = $this->controlStructures['functions'][$controlStructure]['complete'];
                        
                        $output .= $this->$function($content);
                    }
                    else
                    {
                        if (isset($this->controlStructures['functions'][$controlStructure]['top']))
                        {
                            $function = $this->controlStructures['functions'][$controlStructure]['top'];
                            $output .= $this->$function($content);
                        }
                        
                        $output .= $this->executeLayer($content);
                        
                        if (isset($this->controlStructures['functions'][$controlStructure]['bottom']))
                        {
                            $function = $this->controlStructures['functions'][$controlStructure]['bottom'];
                            $output .= $this->$function($content);
                        }
                    }
                }
            }
            
            return $output;
            
        } // executeLayer
        
        private function execute_setCompilerOutputType($type)
        {
            if ($type == $this->execute_lastOutputType)
            {
                return '';
            }
            
            if ($type == GoodLookingCompilerOutputTypes::text && 
                        $this->execute_lastOutputType == GoodLookingCompilerOutputTypes::php)
            {
                $this->execute_lastOutputType = GoodLookingCompilerOutputTypes::text;
                
                return '?>';
            }
            
            if ($type == GoodLookingCompilerOutputTypes::php && 
                        $this->execute_lastOutputType == GoodLookingCompilerOutputTypes::text)
            {
                $this->execute_lastOutputType = GoodLookingCompilerOutputTypes::php;
                
                return '<?php ';
            }
        } // execute_setCompilerOutputType
        
        private function controlStructure_if($layerNumber)
        {
            $output = $this->execute_setCompilerOutputType(GoodLookingCompilerOutputTypes::php);
            
            $conditionEvaluated = 
                    $this->evaluate($this->execute_layers[$layerNumber]['condition']);
            
            $output .= 'if (' . $conditionEvaluated . '){';
            
            return $output;
            
        } // controlStructure_if
        
        private function controlStructure_closeBracket($layerNumber)
        {
            return $this->execute_setCompilerOutputType(GoodLookingCompilerOutputTypes::php) . '}';
        }
        
        private function controlStructure_else($layerNumber)
        {
            return 'else {';
        } // controlStructure_else
        
        private function controlStructure_for($layerNumber)
        {
            $output = $this->execute_setCompilerOutputType(GoodLookingCompilerOutputTypes::php);
            
            if (preg_match('/'.GoodLookingRegexes::$controlStructureConditions['for'].'/', 
                 $this->execute_layers[$layerNumber]['condition'], 
                  $values) <= 0)
            {
                $this->throwError(GoodLookingErrorLevels::high,
                                   "Invalid condition for for-loop. Loop was skipped.");
                return '';
            }
            
            $term1 = $this->evaluate($values['term1']);
            $term2 = $this->evaluate($values['term2']);
            
            $delta = '$this->templateVars[' . $this->execute_templateVarCount . ']';
            $this->execute_templateVarCount++;
            $counter = '$this->templateVars[' . $this->execute_templateVarCount . ']';
            $this->execute_templateVarCount++;
            
            $output .= $delta . ' = $this->determineDelta(' . $term1 . ', ' . $term2 . ');';
            
            $output .= 'for (' . $counter . ' = ' . $term1 . ';' . $counter . ' <= ' . $term2 . '; ' . 
                                                        $counter . ' += ' . $delta . '){';
            
            return $output;
            
        } // controlStructure_for
        
        private function controlStructure_foreach($layerNumber)
        {
            $output = $this->execute_setCompilerOutputType(GoodLookingCompilerOutputTypes::php);
            
            if (preg_match('/'.GoodLookingRegexes::$controlStructureConditions['foreach'].'/', 
                 $this->execute_layers[$layerNumber]['condition'], 
                  $values) <= 0)
            {
                $this->throwError(GoodLookingErrorLevels::high,
                                   "Invalid condition for foreach-loop. Loop was skipped.");
                return '';
            }
            
            $varName = $values['varName'];
            $foreachArray = $this->evaluate($values['array']);
            
            $foreachVar = '$this->templateVars[' . $this->execute_templateVarCount . ']';
            $output .= '$this->registerSpecialVar(\'' . $varName . '\', ' . $this->execute_templateVarCount . ');';
            $this->execute_templateVarCount++;
            $counter = '$this->templateVars[' . $this->execute_templateVarCount . ']';
            $this->execute_templateVarCount++;
            
            $output .= $counter . ' = 0;';
            $output .= 'foreach (' . $foreachArray . ' as ' . $foreachVar . '){';
            $output .= $counter . '++;';
            
            return $output;
            
        } //  controlStructure_foreach
    }
//}