<?php

class GoodLookingEnvironment
{
	private $templateVars = 0;
	
	public function getTemplateVar()
	{
		$this->templateVars++;
		
		return $this->templateVars - 1;
	}
}

?>