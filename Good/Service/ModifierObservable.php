<?php

include_once 'Modifier.php';

class GoodServiceModifierObservable implements GoodServiceModifier
{	
	public function __construct()
	{
	}
	
	public function baseClassTopOfFile()
	{
		$res  = 'include_once $good->getGoodPath() . "/Service/Observer.php";' . "\n";
		$res  = 'include_once $good->getGoodPath() . "/Service/Observable.php";' . "\n";
		$res .= "\n";
		
		return $res;
	}
	
	public function implementingInterfaces()
	{
		return array('GoodServiceObservable');
	}
	
	public function baseClassConstructor()
	{
		$res = "\n";
		$res = '		$this->observers = array();' . "\n";
		
		return $res;
	}
	public function baseClassBody()
	{
		$res  = "	// Observer pattern (Observable)\n";
		$res .= '	private $observers;' . "\n";
		$res .= "	\n";
		$res .= '	public function register(GoodServiceObserver $observer)'. "\n";
		$res .= "	{\n";
		$res .= '		$this->observers[] = $observer;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function unregister(GoodServiceObserver $observer)'. "\n";
		$res .= "	{\n";
		$res .= '		$pos = array_search($observer);' . "\n";
		$res .= '		if ($pos !== FALSE)' . "\n";
		$res .= "		{\n";
		$res .= '			array_splice($this->observers, $pos, 1);' . "\n";
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
	
	public function visitDataModel($dataModel) {}
	
	public function visitDataType($dataType) {}
	
	public function visitDataMember($dataMember) {}
	public function visitTypeReference($type) {}
	public function visitTypePrimitiveText($type) {}
	public function visitTypePrimitiveInt($type) {}
	public function visitTypePrimitiveFloat($type) {}
	public function visitEnd() {}
	
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