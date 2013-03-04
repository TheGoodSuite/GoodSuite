<?php

namespace Good\Looking\AbstractSyntax;

class Environment
{
	private $templateVars = 0;
	
	public function getTemplateVar()
	{
		$this->templateVars++;
		
		return $this->templateVars - 1;
	}
}

?>