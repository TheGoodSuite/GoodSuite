<?php

namespace Good\Service\Modifier;

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
		$res .= '	public function register(\\Good\\Service\\Observer $observer)'. "\n";
		$res .= "	{\n";
		$res .= '		$this->observers[] = $observer;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function unregister(\\Good\\Service\\Observer $observer)'. "\n";
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
		$res .= '			$observer->notify($this);' . "\n";
		$res .= "		}\n";
		$res .= "	}\n";
		$res .= "	\n";
		
		return $res;
	}
	
	public function visitSchema($schema) {}
	public function visitSchemaEnd() {}
	
	public function visitDataType($dataType) {}
	
	public function visitReferenceMember($member) {}
	public function visitTextMember($member) {}
	public function visitIntMember($member) {}
	public function visitFloatMember($member) {}
	public function visitDatetimeMember($member) {}
	
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
	public function nullGetterBegin() {return '';}
	public function nullSetterBegin() {return '';}
	public function nullSetterEnd()
	{
		return $this->setterEnd();
	}
	
	public function topOfFile() {return '';}
	public function classBody() {return '';}
	public function bottomOfFile() {return '';}
	public function extraFiles() {return array();}
}

?>