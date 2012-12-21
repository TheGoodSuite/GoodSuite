<?php

include_once dirname(__FILE__) . '/../Service/Modifier.php';

class GoodMannersModifierStorable implements GoodServiceModifier
{
	private $className;
	private $classMembers;
	private $classVariable;
	private $classVariableIsReference;
	private $report;
	private $classes;
	
	public function __construct()
	{
		$this->classes = array();
	}
	
	public function baseClassTopOfFile()
	{
		// TODO: make ESPECIALLY this include location independent
		$res  = "include_once '../../../Manners/Storable.php';\n";
		$res .= "include_once '../../../Manners/Store.php';\n";
		$res .= "include_once '../../../Manners/Report/ReferenceValue.php';\n";
		$res .= "include_once '../../../Manners/Report/TextValue.php';\n";
		$res .= "include_once '../../../Manners/Report/IntValue.php';\n";
		$res .= "include_once '../../../Manners/Report/FloatValue.php';\n";
		$res .= "include_once 'Reparser.php';\n";
		$res .= "\n";
		
		return $res;
	}
	
	public function implementingInterfaces()
	{
		return array('GoodServiceMannersStorable');
	}
	
	public function baseClassConstructor()
	{
		$res  = "\n";
		$res .= '		$this->deleted = false;' . "\n";
		$res .= '		$this->store = null;' . "\n";
		$res .= '		$this->validationToken = null;' . "\n";
		$res .= '		$this->blank = false;' . "\n";
		$res .= '		$this->isNew = true;' . "\n";
		$res .= '		$this->dirty = false;' . "\n";
		
		return $res;
	}
	public function baseClassBody()
	{
		$res  = "	// Storable\n";
		$res .= '	private $deleted;' . "\n";
		$res .= '	private $isNew;' . "\n";
		$res .= '	private $store;' . "\n";
		$res .= '	private $validationToken;' . "\n";
		$res .= '	private $blank;' . "\n";
		$res .= '	private $id;' . "\n";
		$res .= '	private $dirty;' . "\n";
		$res .= "	\n";
		$res .= '	public function isDeleted()'. "\n";
		$res .= "	{\n";
		$res .= '		return $this->deleted;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function delete()'. "\n";
		$res .= "	{\n";
		$res .= '		$this->deleted = true;' . "\n";
		$res .= '		$this->dirty();' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function isNew()'. "\n";
		$res .= "	{\n";
		$res .= '		return $this->deleted;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function setNew($value)'. "\n";
		$res .= "	{\n";
		$res .= '		$this->isNew = $value;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function setStore(Store $store)' . "\n";
		$res .= "	{\n";
		$res .= '		$this->store = $store;' . "\n";
		$res .= '		if ($this->isNew)' . "\n";
		$res .= "		{\n";
		$res .= '			$this->store->setNew($this);' . "\n";
		$res .= "		}\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function getStore()' . "\n";
		$res .= "	{\n";
		$res .= '		return $this->store;' . "\n";
		$res .= "	}\n";
		$res .= '	public function isBlank()'. "\n";
		$res .= "	{\n";
		$res .= '		return $this->blank;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function setValidationToken(Store $store)' . "\n";
		$res .= "	{\n";
		$res .= '		$this->validationToken = $store->getValidationToken()' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	protected function dirty()' . "\n";
		$res .= "	{\n";
		$res .= '		if (!$this->isDirty())' . "\n";
		$res .= "		{\n";
		$res .= '			$this->dirty = true;' . "\n";
		$res .= '			$this->store->dirty($this);' . "\n";
		$res .= "		}\n";
		$res .= "	}\n";
		$res .= '	public function isDirty()' . "\n";
		$res .= "	{\n";
		$res .= '		return $this->dirty;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	protected function checkValidationToken()' . "\n";
		$res .= "	{\n";
		$res .= '		if (!$this->validationToken->value())' . "\n";
		$res .= "		{\n";
						// TODO: turn this into decent error handling
		$res .= '			die("Tried to acces an invalid Storable. It was probably made invalid by actions" .' . "\n";
		$res .= '		 	    " on its store (like doing a modify, which invalidates all its Storables).")' . "\n";
		$res .= "		}\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function getId()' . "\n";
		$res .= "	{\n";
		$res .= '		return $this->id;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function setId($value)' . "\n";
		$res .= "	{\n";
		$res .= '		$this->id = $value;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		
		return $res;
	}
	
	public function visitDataModel($dataModel) {}
	
	public function visitDataType($dataType)
	{
		$this->className = $dataType->getName();
		$this->classes[] = $this->className;
		$this->classMembers = array();
		
		$this->report  = '	public function report()' . "\n";
		$this->report .= "	{\n";
		$this->report .= '		return doReport('');' . "\n";
		$this->report .= "	}\n";
		$this->report .= "	\n";
		$this->report  = '	public function doReport($name)' . "\n";
		$this->report .= "	{\n";
		$this->report .= '		$members = array();' . "\n";
		$this->report .= "		\n";
	}
	
	public function visitDataMember($dataMember)
	{
		$this->classVariable = $dataMember->getName();
		$this->classMembers[] = $this->classVariable;
	}
	public function visitTypeReference($type)
	{
		$this->classVariableIsReference = true;
		
		// ucfirst: upper case first letter (it's a php built-in)
		$this->report .= '		if ($this->isResolved' ucfirst($this->classVariable) . ' && ' .
									'!$this->isNull' .  ucfirst($this->classVariable) . ')' . "\n";
		$this->report .= "		{\n";
		$this->report .= '			$members[] = $this->' . $this->classVariable . '->doReport("' . $this->classVariable . '");' . "\n";
		$this->report .= "		}\n";
		$this->report .= "		else\n";
		$this->report .= "		{\n";
		$this->report .= '			$members[] = new GoodMannersReferenceValue(true, $this->isDirty' . 
										ucfirst($this->classVariable) ', '', false, null, ' . $type->getReferencedType() . ', array())' . "\n";
		$this->report .= "		}\n";
	}
	public function visitTypePrimitiveText($type) 
	{
		$this->classVariableIsReference = false;
		
		// ucfirst: upper case first letter (it's a php built-in)
		$this->report .= '		$members[] = new GoodMannersTextValue($this->isNull' . ucfirst($this->classVariable) . 
									',$this->isDirty' . ucfirst($this->classVariable) . ', "' . $this->classVariable . '"' .
									 ', $this->' . $this->classVariable . ');' . "\n";
	}
	public function visitTypePrimitiveInt($type) 
	{
		$this->classVariableIsReference = false;
		
		// ucfirst: upper case first letter (it's a php built-in)
		$this->report .= '		$members[] = new GoodMannersIntValue($this->isNull' . ucfirst($this->classVariable) . 
									',$this->isDirty' . ucfirst($this->classVariable) . ', "' . $this->classVariable . '"' .
							', $this->' . $this->classVariable . ');' . "\n";
	}
	public function visitTypePrimitiveFloat($type) 
	{
		$this->classVariableIsReference = false;
		
		// ucfirst: upper case first letter (it's a php built-in)
		$this->report .= '		$members[] = new GoodMannersFloatValue($this->isNull' . ucfirst($this->classVariable) . 
									',$this->isDirty' . ucfirst($this->classVariable) . ', "' . $this->classVariable . '"' .
							', $this->' . $this->classVariable . ');' . "\n";
	}
	
	public function varDefinitionBefore() {return '';}
	public function varDefinitionAfter() 
	{
		// ucfirst: upper case first letter (it's a php built-in)
		$res  = '	private isDirty' . ucfirst($this->classVariable) . ';' . "\n";
		$res .= "	\n";
		$res .= '	public function isDirty' . ucfirst($this->classVariable) . '()' . "\n";
		$res .= "	{\n";
		$res .= '		return $this->isDirty' . ucfirst($this->classVariable) . ';' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function makeDirty' . ucfirst($this->classVariable) . '($value = true)' . "\n";
		$res .= "	{\n";
		$res .= '		$this->isDirty' . ucfirst($this->classVariable) . ' = $value;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		if ($this->classVariableIsReference)
		{
			$res .= '	private $resolved' . ucfirst($this->varName) . " = false;\n";
			$res .= "	\n";
			$res .= '	public function isResolved' . ucfirst($this->varName) . '()' . ";\n";
			$res .= "	{\n";
			$res .= '		return $this->resolved' . ucfirst($this->varName) . ";\n";
			$res .= "	}\n";
			$res .= "	\n";
		}
		
		return $res;
	}
	public function getterBegin()
	{
		$res  = "		checkValidationToken();\n";
		$res .= "		\n";
		
		if ($this->classVariableIsReference)
		{
			$res  .= '		if (!this->isResolved' . ucfirst($this->varName) . '())' . ";\n";
			$res  .= "		{\n";
						// TODO: Make this into a real error
			$res  .= '			die("Tried to access nonresolved property.")' . ";\n";
			$res  .= "		}\n";
			$res  .= "		\n";
		}
		
		return $res;
	}
	public function setterBegin()
	{
		$res .= "		checkValidationToken();\n";
		$res  = "		\n";
		
		return $res;
	}
	public function setterEnd()
	{
		$res  = "		\n";
		// ucfirst: upper case first letter (it's a php built-in)
		$res .= '		$this->makeDirty' . ucfirst($this->classVariable) . '();' . "\n";
		$res .= '		dirty();' . "\n";
		if ($this->classVariableIsReference)
		{
			$res .= '		if ($value != null || $this->isNull' . ucfirst($this->classVariable) . ')' . "\n";
			$res .= "		{\n";
			$res .= '			$this->' . ucfirst($this->classVariable) . ' = true;' . "\n";
			$res .= "		}\n";
		}
		
		return $res;
	}
	public function nullGetterBegin()
	{
		return $this->getterBegin();
	}
	public function nullSetterBegin()
	{
		return $this->setterBegin();
	}
	public function nullSetterEnd()
	{
		return $this->setterEnd();
	}
	
	public function topOfFile() {return '';}
	public function classBody() 
	{
		$res  = '	public static function getBlank()' . "\n";
		$res .= "	{\n";
		$res .= '		$ret = new ' . $this->className . '();' . "\n";
		$res .= "		\n";
		
		foreach ($this->classMembers as $member)
		{
			// ucfirst: upper case first letter (it's a php built-in)
			$res .= '		$ret->' . $member . ' = null;' . "\n";
			$res .= '		$ret->isDirty' . ucfirst($member) . ' = false;' . "\n";
			$res .= '		$ret->isNull' . ucfirst($member) . ' = true;' . "\n";
		}
		
		$res .= '		$ret->blank = true;' . "\n";
		$res .= "		\n";
		$res .= '		return $ret;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		
		$res .= '	public function reparse(Reparser $reparser)' . "\n";
		$res .= "	{\n";
		$res .= '		$reparser->reparse' . ucfirst($this->className) . '($this);' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		
		$this->report .= "		\n";
		$this->report .= ' 		return new GoodMannersReferenceValue(false, true, $this, $name "' . $this->className . '", $members)' . "\n";
		$this->report .= "	}\n";
		$this->report .= "	\n";
		
		$res .= $this->report;
		
		return $res;
	}
	public function bottomOfFile() {return '';}
	
	public function extraFiles()
	{
		$res  = '<?php' . "\n";
		$res .= "\n";
		$res .= 'interface GoodMannersReparser' . "\n";
		$res .= "{\n";
		foreach ($this->classes as $aClass)
		{
			// ucfirst: upper case first letter (php builtin)
			$res .= '	public function reparse' . ucfirst($aClass) . '(' . $aClass . ' $storable);' . "\n";
		}
		$res .= "}\n";
		$res .= "\n";
		$res .= "?>";
		
		return array('Reparser.php' => $res);
	}
}

?>