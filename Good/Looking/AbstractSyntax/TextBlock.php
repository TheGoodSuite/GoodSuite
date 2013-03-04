<?php

class GoodLookingTextBlock implements GoodLookingAbstractSyntaxElement
{
	private $text;
	
	public function __construct($text)
	{
		$this->text = $text;
	}
	
	public function execute(GoodLookingEnvironment $environment)
	{
		return '?>' . $this->text . '<?php ';
	}
}

?>