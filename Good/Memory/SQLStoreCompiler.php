<?php

namespace Good\Memory;

class SQLStoreCompiler implements \Good\Rolemodel\Visitor
{
	// Compiler level data
	private $outputDir;
	private $output = null;
	private $includes = array();
	private $create = null;
	private $firstDateType = true;
	
	private $varName;
	private $dataType;
	
	private $flush;
	
	public function __construct($outputDir)
	{
		$this->outputDir = $outputDir;
	}
	
	public function visitDataModel($dataModel)
	{
		// Start off the class 
		$this->output  = 'class GoodMemorySQLStore extends \\Good\\Memory\\BaseSQLStore' . "\n";
		$this->output .= "{\n";
		$this->output .= '	public function __construct(\\Good\\Memory\\Database\\Database $db)' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		parent::__construct($db);' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		
		$this->flush  = '	public function flush()' . "\n";
		$this->flush .= "	{\n";
	}
	
	public function visitDataType($dataType)
	{
		if ($this->firstDateType)
		{
			$this->firstDateType = false;
		}
		else
		{
			$this->finishDataType();
		}
		
		$name = $dataType->getName();
		$this->dataType = $name;
		
		// ucfirst: upper case first (php builtin)
		$this->output .= '	protected function saveNew' . \ucfirst($name) . 's(array $entries)' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		$this->saveAnyNew("' . $name . '", $entries);' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		$this->output .= '	protected function save' . \ucfirst($name) . 
														'Modifications(array $entries)' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		$this->saveAnyModifications("' . $name . '", $entries);' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		$this->output .= '	protected function save' . \ucfirst($name) . 'Deletions(array $entries)' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		$this->saveAnyDeletions("' . $name . '", $entries);' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		$this->output .= '	protected function doGet' . \ucfirst($name) .
							'Collection(\\Good\\Manners\\Condition $condition, ' . $name . 
																	'Resolver $resolver)' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		$res = $this->doAnyGet("' . $name . '", $condition, $resolver);' . "\n";
		$this->output .= '		return new ' . $name . 'Collection($this, $res);' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		$this->output .= '	protected function doModifyAny' . \ucfirst($name) . 
							'(\\Good\\Manners\\Condition $condition, ' . $name . ' $modifications)' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		$this->doAnyModify("' . $name . '", $condition, $modifications);' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		
		$this->create  = '	private $created' . \ucfirst($name) . 's = array();' . "\n";
		$this->create .= "	\n";
		$this->create .= '	public function create' . \ucfirst($name) .
														'(array $array, $table = "t0", &$nextTable = 0)' . "\n";
		$this->create .= "	{\n";
		$this->create .= '		$nextTable++;' . "\n";
		$this->create .= "		\n";
		$this->create .= '		if (array_key_exists($array[$table . "_id"], ' .
															'$this->created' . \ucfirst($name) . 's))' . "\n";
		$this->create .= "		{\n";
		$this->create .= '			return $this->created' . \ucfirst($name) . 
													's[$array[$this->tableNamify($table) . "_id"]];' . "\n";
		$this->create .= "		}";
		$this->create .= "		\n";
		$this->create .= '		$ret = ' . $name . '::createExisting($this, $array[$table . "_id"]';
		
		$out  = '<?php' . "\n";
		$out .= "\n";
		$out .= 'class ' . $name . 'Collection extends \\Good\\Memory\\BaseCollection' . "\n";
		$out .= "{\n";
		$out .= '	public function getNext()' . "\n";
		$out .= "	{\n";
		$out .= '		if ($row = $this->dbresult->fetch())' . "\n";
		$out .= "		{\n";
		$out .= '			return $this->store->create' . \ucfirst($name) . '($row);' . "\n";
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
		$this->finishDataType();
		
		// neatly start the file
		$top  = "<?php\n";
		$top .= "\n";
		
		foreach ($this->includes as $include)
		{
			// TODO: Dirty, but better than previously (which had a hardcoded location)
			$top .= 'require_once dirname(__FILE__) . "/' . $include . '";' . "\n";
		}
		
		$top .= "\n";
		
		$this->output = $top . $this->output;
		
		$this->flush .= "		\n";
		$this->flush .= '		parent::flush();' . "\n";
		$this->flush .= "	}\n";
		$this->flush .= "	\n";
		
		$this->output .= $this->flush;
		
		// close the file off
		$this->output .= "}\n";
		$this->output .= "\n";
		$this->output .= "?>";
		
		file_put_contents($this->outputDir . 'SQLStore.php', $this->output);
	}
	
	public function visitDataMember($dataMember) 
	{
		$this->varName = $dataMember->getName();
	}
	
	public function visitTypeReference($type)
	{
		$this->create .= ",\n";
		// TODO: spread this (and all arguments) over multiple lines in output
		$this->create .= '			\array_key_exists($table . "_" . $this->fieldNamify("' . 
																$this->varName . '"),' . "\n"; 
		$this->create .= '					$array) && $array[$table . "_" . $this->fieldNamify("' . 
																				$this->varName . "\")]\n";
		$this->create .= '					 !== null ? $this->create' . 
												ucfirst($type->getReferencedType()) . 
												'($array, "t" . $nextTable, $nextTable) : null';
	}
	public function visitTypePrimitiveText($type)
	{
		$this->visitNonReference();
	}
	public function visitTypePrimitiveInt($type)
	{
		$this->visitNonReference();
	}
	public function visitTypePrimitiveFloat($type)
	{
		$this->visitNonReference();
	}
	public function visitTypePrimitiveDatetime($type)
	{
		// I should look into abstracting this more in order to make it work
		// better with more SQL implementations, but I think this will do for now
		$this->create .= ",\n";
		$this->create .= '			$array[$table . "_" . $this->fieldNamify("' . 
										$this->varName . '")] === null ? null : new DateTime($array[$table . ' .
  										  '"_" . $this->fieldNamify("' . $this->varName . '")])';
	}
	
	private function visitNonReference()
	{
		$this->create .= ",\n";
		$this->create .= '			$array[$table . "_" . $this->fieldNamify("' . $this->varName . '")]';
	}
	
	private function finishDataType()
	{
		$this->create .= "\n";
		$this->create .= '		);' . "\n";
		$this->create .= '		$this->created' . ucfirst($this->dataType) . 's[$array[$table . "_id"]] = $ret;' . "\n";
		$this->create .= "		\n";
		$this->create .= '		return $ret;' . "\n";
		$this->create .= "	}\n";
		$this->create .= "	\n";
		
		$this->output .= $this->create;
		
		$this->flush .= '		$this->created' . ucfirst($this->dataType) . 's = array();' . "\n";
	}
}

?>