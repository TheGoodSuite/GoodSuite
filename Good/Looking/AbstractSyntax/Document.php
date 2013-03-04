<?php

class GoodLookingAbstractDocument implements GoodLookingAbstractSyntaxElement
{
	private $statements;
	
	public function __construct($statements)
	{
		$this->statements = $statements;
	}
	
	public function execute(GoodLookingEnvironment $environment)
	{
		$out = '<?php ';
		
		foreach ($this->statements as $statement)
		{
			$out .= $statement->execute($environment);
		}
		
		$out .= '?>';
		
		return $out;
	}
}

?>