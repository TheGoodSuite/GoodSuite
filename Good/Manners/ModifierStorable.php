<?php

include_once dirname(__FILE__) . '/../Service/Modifier.php';

class GoodMannersModifierStorable implements GoodServiceModifier
{
	private $className;
	private $classMembers;
	private $classVariable;
	private $classVariableIsReference;
	private $acceptStore;
	
	public function __construct()
	{
	}
	
	public function baseClassTopOfFile()
	{
		$res  = 'require_once $good->getGoodPath() . "/Manners/Storable.php";' . "\n";
		$res .= "\n";
		
		return $res;
	}
	
	public function implementingInterfaces()
	{
		return array('GoodMannersStorable');
	}
	
	public function baseClassConstructor()
	{
		return '';
	}
	public function baseClassBody()
	{
		$res  = "	// Storable\n";
		$res .= '	private $deleted = false;' . "\n";
		$res .= '	private $isNew = true;' . "\n";
		$res .= '	protected $store = null;' . "\n";
		$res .= '	private $validationToken = null;' . "\n";
		$res .= '	private $id = -1;' . "\n";
		$res .= '	private $dirty = false;' . "\n";
		$res .= "	\n";
		$res .= '	abstract protected function dirty();' . "\n";
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
		$res .= '		return $this->isNew;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function setNew($value)'. "\n";
		$res .= "	{\n";
		$res .= '		$this->isNew = $value;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function setStore(GoodMannersStore $store)' . "\n";
		$res .= "	{\n";
		$res .= '		$this->store = $store;' . "\n";
		$res .= '		if ($this->isNew)' . "\n";
		$res .= "		{\n";
						// Just comented it out, as it looks like it's not in line with
						//  the currently planned API.
		$res .= '			//$this->store->setNew($this);' . "\n";
		$res .= "		}\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function storeMatches(GoodMannersStore $store)' . "\n";
		$res .= "	{\n";
		$res .= '		if ($store == $this->store)' . "\n";
		$res .= "		{\n";
		$res .= '			return true;' . "\n";
		$res .= "		}\n";
		$res .= '		else' . "\n";
		$res .= "		{\n";
		$res .= '			return false;' . "\n";
		$res .= "		}\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function setValidationToken(GoodMannersValidationToken $token)' . "\n";
		$res .= "	{\n";
		$res .= '		$this->validationToken = $token;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function isDirty()' . "\n";
		$res .= "	{\n";
		$res .= '		return $this->dirty;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function makeDirty($value = true)' . "\n";
		$res .= "	{\n";
		$res .= '		return $this->dirty = $value;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	protected function checkValidationToken()' . "\n";
		$res .= "	{\n";
		$res .= '		if ($this->validationToken != null &&!$this->validationToken->value())' . "\n";
		$res .= "		{\n";
						// TODO: turn this into decent error handling
		$res .= '			die("Tried to acces an invalid Storable. It was probably made invalid by actions" .' . "\n";
		$res .= '		 	    " on its store (like doing a modify, which invalidates all its Storables).");' . "\n";
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
		$res .= '	public static function helpCreatingExisting(GoodMannersStorable $value, $id)' . "\n";
		$res .= "	{\n";
		$res .= '		$value->id = $id;' . "\n";
		$res .= '		$value->isNew = false;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		
		return $res;
	}
	
	public function visitDataModel($dataModel) {}
	
	public function visitDataType($dataType)
	{
		$this->className = $dataType->getName();
		$this->classMembers = array();
		
		$this->acceptStore  = '	public function acceptStore(GoodMannersStore $store)' . "\n";
		$this->acceptStore .= "	{\n";
		$this->acceptStore .= '		if (!$this->storeMatches($store) && !$this->isNew())' . "\n";
		$this->acceptStore .= "		{\n";
		// TODO: turn this into real error handling
		$this->acceptStore .= '			die("Error: Attempted to use Storable with Store that" . ' . "\n";
		$this->acceptStore .= '				  " is not its own.");' . "\n";
		$this->acceptStore .= "		}\n";
		$this->acceptStore .= "		\n";
	}
	
	public function visitDataMember($dataMember)
	{
		$this->classVariable = $dataMember->getName();
		$this->classMembers[] = $this->classVariable;
	}
	public function visitTypeReference($type)
	{
		$this->classVariableIsReference = true;
		
		$this->acceptStore .= '		$store->visitReferenceProperty("' . $this->classVariable . '", ' .
											'"' . $type->getReferencedType() . '", ' . 
											'$this->is' . ucfirst($this->classVariable) . 'Dirty(), ' .
											'$this->is' . ucfirst($this->classVariable) . 'Null(), ' .
											'$this->get' . ucfirst($this->classVariable) . '());' . "\n";
	}
	public function visitTypePrimitiveText($type) 
	{
		$this->classVariableIsReference = false;
		
		$this->acceptStore .= '		$store->visitTextProperty("' . $this->classVariable . '", ' .
											'$this->is' . ucfirst($this->classVariable) . 'Dirty(), ' . 
											'$this->is' . ucfirst($this->classVariable) . 'Null(), ' .
											'$this->get' . ucfirst($this->classVariable) . '());' . "\n";
	}
	public function visitTypePrimitiveInt($type) 
	{
		$this->classVariableIsReference = false;
		
		$this->acceptStore .= '		$store->visitIntProperty("' . $this->classVariable . '", ' .
											'$this->is' . ucfirst($this->classVariable) . 'Dirty(), ' . 
											'$this->is' . ucfirst($this->classVariable) . 'Null(), ' .
											'$this->get' . ucfirst($this->classVariable) . '());' . "\n";
	}
	public function visitTypePrimitiveFloat($type) 
	{
		$this->classVariableIsReference = false;
		
		$this->acceptStore .= '		$store->visitFloatProperty("' . $this->classVariable . '", ' .
											'$this->is' . ucfirst($this->classVariable) . 'Dirty(), ' . 
											'$this->is' . ucfirst($this->classVariable) . 'Null(), ' .
											'$this->get' . ucfirst($this->classVariable) . '());' . "\n";
	}
	
	public function visitEnd() {}
	
	public function varDefinitionBefore() {return '';}
	public function varDefinitionAfter() 
	{
		// ucfirst: upper case first letter (it's a php built-in)
		$res  = '	private $is' . ucfirst($this->classVariable) . 'Dirty;' . "\n";
		$res .= "	\n";
		$res .= '	public function is' . ucfirst($this->classVariable) . 'Dirty()' . "\n";
		$res .= "	{\n";
		$res .= '		return $this->is' . ucfirst($this->classVariable) . 'Dirty;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function make' . ucfirst($this->classVariable) . 'Dirty($value = true)' . "\n";
		$res .= "	{\n";
		$res .= '		$this->is' . ucfirst($this->classVariable) . 'Dirty = $value;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		
		return $res;
	}
	public function getterBegin()
	{
		$res  = '		$this->checkValidationToken();' . "\n";
		$res .= "		\n";
		
		return $res;
	}
	public function setterBegin()
	{
		$res  = '		$this->checkValidationToken();' . "\n";
		$res .= "		\n";
		
		return $res;
	}
	public function setterEnd()
	{
		$res  = "		\n";
		// ucfirst: upper case first letter (it's a php built-in)
		$res .= '		$this->make' . ucfirst($this->classVariable) . 'Dirty();' . "\n";
		$res .= '		$this->dirty();' . "\n";
		if ($this->classVariableIsReference)
		{
			$res .= '		if ($value != null || $this->is' . ucfirst($this->classVariable) . 'Null)' . "\n";
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
	
	public function topOfFile()
	{
		$res  = 'require_once "' . $this->className . 'Resolver.php";' . "\n";
		$res .= "\n";
		
		return $res;
	}
	
	public function classBody()
	{
		$res  = '	protected function dirty()' . "\n";
		$res .= "	{\n";
		$res .= '		if (!$this->isDirty() && $this->store != null)' . "\n";
		$res .= "		{\n";
		$res .= '			$this->makeDirty(true);' . "\n";
		$res .= '			$this->store->dirty' . ucfirst($this->className) . '($this);' . "\n";
		$res .= "		}\n";
		$res .= "	}\n";
		
		$res  .= '	public static function createExisting($store, $id';
		foreach ($this->classMembers as $member)
		{
			$res .= ', ';
			// ucfirst: upper case first letter (it's a php built-in)
			$res .= '$' . $member;
		}
		$res .= ')' . "\n";
		$res .= "	{\n";
		$res .= '		$ret = new ' . $this->className . '();' . "\n";
		$res .= '		GeneratedBaseClass::helpCreatingExisting($ret, $id);' . "\n";;
		$res .= "		\n";
		foreach ($this->classMembers as $member)
		{
			// ucfirst: upper case first letter (it's a php built-in)
			$res .= '		$ret->' . $member . ' = $' . $member . ';' . "\n";
			$res .= '		$ret->is' . ucfirst($member) . 'Dirty = false;' . "\n";
			// TODO: make this typesafe (currently, primitives can be NULL)
			$res .= '		$ret->is' . ucfirst($member) . 'Null = $' . $member . ' === NULL;' . "\n";
		}
		
		$res .= '		$ret->setStore($store);' . "\n";
		$res .= "		\n";
		$res .= '		return $ret;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		
		$res .= '	public static function resolver()' . "\n";
		$res .= "	{\n";
		$res .= '		return new ' . $this->className . 'Resolver();' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		
		$this->acceptStore .= "	}\n";
		$this->acceptStore .= "	\n";
		
		$res .= $this->acceptStore;
		
		return $res;
	}
	public function bottomOfFile() {return '';}
	
	public function extraFiles() {return array();}
}

?>