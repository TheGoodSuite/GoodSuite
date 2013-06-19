<?php

namespace Good\Looking;

class Looking
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
		if (\preg_match('/'. Regexes::$varName .'/', $varName) > 0 &&
			 \preg_match('/'. Regexes::$controlStructure .'/', $varName) == 0)
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
		if (!\file_exists($this->templateFileName))
		{
			throw new \Exception('Template not found.');
		}
		
		if (!\file_exists($this->templateFileName . '.compiledTemplate') ||
					\filemtime($this->templateFileName) > \filemtime($this->templateFileName . '.compiledTemplate'))
		{
			$compiler = new Compiler();
			$compiler->compile($this->templateFileName, $this->templateFileName . '.compiledTemplate');
		}
		
		$interpreter = new Interpreter($this->templateFileName . '.compiledTemplate', 
									   $this->registeredVars);
		
		$interpreter->interpret();
		
	} // display
} // GoodLooking