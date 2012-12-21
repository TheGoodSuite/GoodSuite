<?php

require_once dirname(__FILE__) . '/../Rolemodel/Visitor.php';


class GoodMemorySQLStoreCompiler implements GoodRolemodelVisitor
{
	// Compiler level data
	private $outputDir;
	private $output = null;
	private $includes = array();
	
	public function __construct($outputDir)
	{
		$this->outputDir = $outputDir;
	}
	
	public function visitDataModel($dataModel)
	{
		// Start off the class 
		$this->output  = 'class GoodMemorySQLStore extends GoodMemoryBaseSQLStore' . "\n";
		$this->output .= "{\n";
		$this->output .= '	public function __construct(GoodMemoryDatabase $db)' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		parent::__construct($db);' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
	}
	
	public function visitDataType($dataType)
	{
		$name = $dataType->getName();
		
		// ucfirst: upper case first (php builtin)
		$this->output .= '	protected function saveNew' . ucfirst($name) . 's(array $entries)' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		$this->saveAnyNew("' . $name . '", $entries);' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		$this->output .= '	protected function save' . ucfirst($name) . 
														'Modifications(array $entries)' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		$this->saveAnyModifications("' . $name . '", $entries);' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		$this->output .= '	protected function save' . ucfirst($name) . 'Deletions(array $entries)' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		$this->saveAnyDeletions("' . $name . '", $entries);' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		$this->output .= '	protected function doGet' . ucfirst($name) .
							'Collection(GoodMannersCondition $condition, ' . $name . ' $resolver)' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		$res = $this->doAnyGet("' . $name . '", $condition, $resolver);' . "\n";
		$this->output .= '		return new ' . $name . 'Collection($this, $res);' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		$this->output .= '	protected function doModifyAny' . ucfirst($name) . 
							'(GoodMannersCondition $condition, ' . $name . ' $modifications)' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		$this->doAnyModify("' . $name . '", $condition, $modifications);' . "\n";
		$this->output .= "	}\n";
		
		
		$out  = '<?php' . "\n";
		$out .= "\n";
		$out .= 'require_once $good->getGoodPath() . "/Memory/BaseCollection.php";' . "\n";
		$out .= "\n";
		$out .= 'class ' . $name . 'Collection extends GoodMemoryBaseCollection' . "\n";
		$out .= "{\n";
		$out .= '	public function getNext()' . "\n";
		$out .= "	{\n";
		$out .= '		if ($row = $this->dbresult->fetch())' . "\n";
		$out .= "		{\n";
		$out .= '			return $this->store->create' . ucfirst($name) . '($row);' . "\n";
		$out .= "		}\n";
		$out .= '		else' . "\n";
		$out .= "		{\n";
		$out .= '			return null;' . "\n";
		$out .= "		}\n";
		$out .= "	}\n";
		$out .= "}\n";
		$out .= "\n";
		$out .= "?>";
		
		file_put_contents($this->outputDir . $name . 'Collection.php', $out);
		
		$this->includes[] = $name . 'Collection.php';
	}
	
	public function visitEnd()
	{
		// neatly start the file
		$top  = "<?php\n";
		$top .= "\n";
		
		$top .= 'require_once $good->getGoodPath() . "/Memory/BaseSQLStore.php";' . "\n";
		$top .= "\n";
		
		foreach ($this->includes as $include)
		{
			// TODO: Make this location independent
			$top .= 'require_once "compiled/' . $include . '";' . "\n";
		}
		
		$top .= "\n";
		
		$this->output = $top . $this->output;
		
		// close the file off
		$this->output .= "}\n";
		$this->output .= "\n";
		$this->output .= "?>";
		
		file_put_contents($this->outputDir . 'SQLStore.php', $this->output);
	}
	
	public function visitDataMember($dataMember) {}
	public function visitTypeReference($type) {}
	public function visitTypePrimitiveText($type) {}
	public function visitTypePrimitiveInt($type) {}	
	public function visitTypePrimitiveFloat($type) {}
}

?>