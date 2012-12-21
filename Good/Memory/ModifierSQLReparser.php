<?php

require_once dirname(__FILE__) . '/../Service/Modifier.php';

class GoodMemoryModifierSQLReparser extends GoodRolemodelVisitor
{
	private $constructor;
	private $body;
	private $reparse;
	private $returnValue;
	
	public function __construct()
	{
		$this->constructor = '';
		$this->body = '';
		$this->reparse = '';
	}
	
	public function visitDataModel($dataModel)
	{
		$this->constructor .= '		$this->pointer = 0;' . "\n";
		$this->constructor .= "		\n";
		
		$this->body .= '	private $pointer;' . "\n";
		$this->body .= "	\n";
	}
	public function visitDataType($dataType) {}
	{
		$this->constructor .= '		$this->' . $dataType->getName() . 's = array();' . "\n";
		$this->constructor .= '		$this->reparsed = array();' . "\n";
		$this->constructor .= '		$this->currentTable = 0;' . "\n";
		$this->constructor .= '		$this->highestTable = 0;' . "\n";
		
		$this->body .= '	private $' $dataType->getName() . 's;' . "\n";
		$this->body .= '	private $reparsed;' . "\n";
		$this->body .= '	private $currentTable;' . "\n";
		$this->body .= '	private $highestTable;' . "\n";
		$this->body .= "	\n";
		// ucfirst: upper case first letter (php builtin)
		$this->body .= '	public function getNext' . ucfirst($dataType->getName()) . '()' . "\n";
		$this->body .= "	{\n";
		$this->body .= '		if ($this->pointer >= count($this->' . ucfirst($dataType->getName()) . 's))' . "\n";
		$this->body .= "		{\n";
		$this->body .= '			return null;' . "\n";
		$this->body .= "		}\n";
		$this->body .= '		' . "\n";
		$this->body .= '		$this->pointer++;' . "\n";
		$this->body .= "		\n";
		$this->body .= '		return $this->' . ucfirst($dataType->getName()) . 's[$this->pointer - 1];' . "\n";
		$this->body .= "	}\n";
		$this->body .= "	\n";
		
		$this->reparse .= '	public function reparse' . ucfirst($dataType->getName()) . '(' . ucfirst($dataType->getName()) . ' $storable)' . "\n";
		$this->reparse .= "	{\n";
		$this->reparse .= '		if (!$storable->)' . "\n";
		$this->reparse .= '		if (isset($this->reparsed["' . $dataType->getName() .'"][$storable->getId()]))' . "\n";
		$this->reparse .= "		{\n";
		$this->reparse .= '			$this->returnValue =& $this->reparsed["' . $dataType->getName() .'"];' . "\n";
		$this->reparse .= "		}\n";
		$this->reparse .= "		else\n";
		$this->reparse .= "		{\n";
		$this->reparse .= '			' . "\n";
	}
	public function visitDataMember($dataMember) {}
	public function visitTypeReference($type) {}
	public function visitTypePrimitiveText($type) {}
	public function visitTypePrimitiveInt($type) {}
	public function visitTypePrimitiveFloat($type) {}
	
	public function baseClassTopOfFile() {return '';}
	public function implementingInterfaces() {return array();}
	public function baseClassBody() {return '';}
	public function baseClassConstructor() {return '';}
	public function getterBegin() {return '';}
	public function setterBegin() {return '';}
	public function setterEnd() {return '';}
	public function nullGetterBegin() {return '';}
	public function nullSetterBegin() {return '';}
	public function nullSetterEnd() {return '';}
	public function varDefinitionBefore() {return '';}
	public function varDefinitionAfter() {return '';}
	public function topOfFile() {return '';}
	public function bottomOfFile() {return '';}
	
	public function extraFiles()
	{
		$res  = '</php' . "\n";
		$res .= "\n";
		$res .= 'require_once "Reparser.php";' . "\n";
		$res .= "\n";
		$res .= 'class GoodMemorySQLReparser' . "\n";
		$res .= "{\n";
		$res .= '	' . "\n";
		$res .= '	' . "\n";
	}
}

?>

?>