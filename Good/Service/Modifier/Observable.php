<?php

namespace Good\Service\Modifier;

use Good\Rolemodel\Schema;

class Observable implements \Good\Service\Modifier
{	
	public function __construct()
	{
	}
	
	public function baseClassTopOfFile()
	{
		return '';
	}
	
	public function implementingInterfaces()
	{
		return array('\\Good\\Service\\Observable');
	}
	
	public function baseClassConstructor()
	{
		return '';
	}
	public function baseClassBody()
	{
		$res  = "	// Observer pattern (Observable)\n";
		$res .= '	private $observers = array();' . "\n";
		$res .= "	\n";
		$res .= '	public function registerObserver(\\Good\\Service\\Observer $observer)'. "\n";
		$res .= "	{\n";
		$res .= '		$this->observers[] = $observer;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function unregisterObserver(\\Good\\Service\\Observer $observer)'. "\n";
		$res .= "	{\n";
		$res .= '		$pos = \\array_search($observer, $this->observers);' . "\n";
		$res .= '		if ($pos !== FALSE)' . "\n";
		$res .= "		{\n";
		$res .= '			\array_splice($this->observers, $pos, 1);' . "\n";
		$res .= "		}\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= "	protected function notifyObservers()\n";
		$res .= "	{\n";
		$res .= '		foreach ($this->observers as $observer)' . "\n";
		$res .= "		{\n";
		$res .= '			$observer->notifyObserver($this);' . "\n";
		$res .= "		}\n";
		$res .= "	}\n";
		$res .= "	\n";
		
		return $res;
	}
	
	public function visitSchema(Schema $schema) {}
	public function visitSchemaEnd() {}
	
	public function visitDataType(Schema\DataType $dataType) {}
	
	public function visitReferenceMember(Schema\ReferenceMember $member) {}
	public function visitTextMember(Schema\TextMember $member) {}
	public function visitIntMember(Schema\IntMember $member) {}
	public function visitFloatMember(Schema\FloatMember $member) {}
	public function visitDatetimeMember(Schema\DatetimeMember $member) {}
	
	public function varDefinitionBefore() {return '';}
	public function varDefinitionAfter() {return '';}
	public function getterBegin() {return '';}
	public function setterBegin() {return '';}
	public function setterEnd()
	{
		$res  = "		\n";
		$res .= '		$this->notifyObservers();' . "\n";
		
		return $res;
	}
	
	public function topOfFile() {return '';}
	public function classBody() {return '';}
	public function bottomOfFile() {return '';}
	public function extraFiles() {return array();}
}

?>