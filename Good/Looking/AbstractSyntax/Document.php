<?php

namespace Good\Looking\AbstractSyntax;

class Document implements Element
{
	private $statements;
	
	public function __construct($statements)
	{
		$this->statements = $statements;
	}
	
	public function execute(Environment $environment)
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