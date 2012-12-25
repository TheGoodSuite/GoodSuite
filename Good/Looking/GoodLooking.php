<?php

//namespace \Good\Looking
//{
    //error_reporting(E_ALL); 
    
    require_once 'Compiler.php';
    require_once 'Interpreter.php';
    require_once 'Regexes.php';
    require_once 'constants.php';
    
    
    class GoodLooking
    {
        // the name of the template file (is set in constructor)
        private $templateFileName;
        // the variables that the calling file registers with this class
        private $registeredVars = array();
        
        public function __construct($fileName)
        {
            $this->templateFileName = $fileName;
            
        } // __construct
        
        public function registerVar($varName, $varValue)
        {
            if (preg_match('/'. GoodLookingRegexes::$varName .'/', $varName) > 0 &&
                 preg_match('/'. GoodLookingRegexes::$controlStructure .'/', $varName) == 0)
            // if the varName matches variable syntax and does not match a control structure
            {
                $this->registeredVars[$varName] = $varValue;
                
                return true;
            }
            else
            {
                return false;
            }
            
        } // registerVar
        
        public function registerMultipleVars($array)
        {
            foreach ($array as $key => $value)
            {
                $this->registerVar($key, $value);
            }
        } //registerMultipleVariables
        
        public function display()
        {
            if (!file_exists($this->templateFileName))
            {
                $this->throwError(GoodLookingErrorLevels::fatal, 'Template not found.');
            }
            
            if (!file_exists($this->templateFileName . '.compiledTemplate') ||
                        filemtime($this->templateFileName) > filemtime($this->templateFileName))
            {
                $compiler = new GoodLookingCompiler();
                $compiler->compile($this->templateFileName, $this->templateFileName . '.compiledTemplate');
            }
            
            $interpreter = new GoodLookingInterpreter($this->templateFileName . '.compiledTemplate', 
                                                      $this->registeredVars);
            
            $interpreter->interpret();
            
        } // display
        
        
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
                echo "<!-- ";
                echo $prefix . $errorMessage;
                echo " -->";
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
                die;
            }
        } // throwError
    } // GoodLooking
//} // \Good\Looking