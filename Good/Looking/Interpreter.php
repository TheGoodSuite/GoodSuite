<?php

namespace Good\Looking;

class Interpreter
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
		$executionTime = \microtime(true);
		
		include $this->compiledTemplate;
		
		// for testing I wanna know how long this function outputs how long it took
		global $interpretTime;
		$interpretTime = \microtime(true)-$executionTime;
		
	} // interpret
	
	/*---------------------------------------------------------------*/
	/* Functions that should be called by compiled template          */
	/*---------------------------------------------------------------*/
	
	private function registerSpecialVar($varName, $internalNumber)
	{
		$this->specialVars[$varName] = $internalNumber;
	} // registerSpecialVar
	
	private function getVar($varName)
	{
		if (isset($this->specialVars[$varName]))
		{
			return $this->templateVars[$this->specialVars[$varName]];
		}
		
		return $this->registeredVars[$varName];
	} // getVar
	
	private function arrayItem($array, $item)
	{
		return $array[$item];
	} // arrayItem
} // GoodLookingInterpreter