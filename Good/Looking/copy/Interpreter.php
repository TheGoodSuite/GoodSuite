<?php

//namespace \Good\Looking
//{

    class GoodLookingInterpreter
    {
        // the filename of the template file (is set in constructor)
        private $compiledTemplate;
        
        private $templateVars = array();
        // the variables that the calling file registers with this class
        private $registeredVars = array();
        private $specialVars = array();
        // special vars in format: $specialVars['varName'] = internalIndex
        // varName = name in template; varLayer = which layer defines the variable;
        // templateVars[internalIndex] = value of specialVar
        
        /*---------------------------------------------------------------*/
        /* Functions that provide API, should be called by other classes */
        /*---------------------------------------------------------------*/
        
        public function __construct($compiledTemplate, $vars)
        {
            $this->compiledTemplate = $compiledTemplate;
            $this->registeredVars = $vars;
        } // __construct
        
        public function interpret()
        {
            //for testing I really want to know how long this function takes to be executed
            $executionTime = microtime(true);
            
            mysql_connect("mysql8.000webhost.com", "a5400048_me", "passw3");
            mysql_select_db("a5400048_LG");
            
            $result = mysql_query(sprintf("SELECT contents
                                           FROM SQLStream
                                           WHERE name='sampleTemplate.html'"));
            
            $row = mysql_fetch_assoc($result);
            
            eval('?>' . $row['contents'] . '<?php;');
            
            // for testing I wanna know how long this function outputs how long it took
            echo '<!-- Interpreting took:' . (microtime(true)-$executionTime) . ' seconds -->';
            
        } // interpret
        
        /*---------------------------------------------------------------*/
        /* Functions that should be called by compiled template          */
        /*---------------------------------------------------------------*/
        
        public function registerSpecialVar($varName, $internalNumber)
        {
            $this->specialVars[$varName] = $internalNumber;
        } // registerSpecialVar
        
        public function getVar($varName)
        {
            if (isset($this->specialVars[$varName]))
            {
                return $this->templateVars[$this->specialVars[$varName]];
            }
            
            return $this->registeredVars[$varName];
        } // getVar
        
        public function arrayItem($array, $item)
        {
            return $array[$item];
        } // arrayItem
        
        public function determineDelta($term1, $term2)
        {
            if ($term2 < $term1)
            {
                return -1;
            }
            else
            {
                return 1;
            }
        } // determineDelta
    } // GoodLookingInterpreter
//} // \Good\Looking