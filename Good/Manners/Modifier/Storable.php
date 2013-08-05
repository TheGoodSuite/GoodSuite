<?php

namespace Good\Manners\Modifier;

use Good\Rolemodel\Schema;

class Storable implements \Good\Service\Modifier
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
		return '';
	}
	
	public function implementingInterfaces()
	{
		return array('\\Good\\Manners\\Storable');
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
		$res .= '	protected $dirty = false;' . "\n";
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
		$res .= '	public function setStore(\\Good\\Manners\\Store $store)' . "\n";
		$res .= "	{\n";
		$res .= '		$this->store = $store;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function setValidationToken(\\Good\\Manners\\ValidationToken $token)' . "\n";
		$res .= "	{\n";
		$res .= '		$this->validationToken = $token;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function isDirty()' . "\n";
		$res .= "	{\n";
		$res .= '		return $this->dirty;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	abstract public function makeDirty($value = true);' . "\n";
		$res .= "	\n";
		$res .= '	protected function checkValidationToken()' . "\n";
		$res .= "	{\n";
		$res .= '		if ($this->validationToken != null && !$this->validationToken->value())' . "\n";
		$res .= "		{\n";
						// TODO: turn this into decent error handling
		$res .= '			throw new \\Exception("Tried to acces an invalid Storable. It was probably made invalid by actions" .' . "\n";
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
		
		return $res;
	}
	
	public function visitSchema(Schema $schema) {}
	public function visitSchemaEnd() {}
	
	public function visitDataType(Schema\DataType $dataType)
	{
		$this->className = $dataType->getName();
		$this->classMembers = array();
		
		$this->acceptStore  = '	public function acceptStore(\\Good\\Manners\\Store $store)' . "\n";
		$this->acceptStore .= "	{\n";
		$this->acceptStore .= "		\n";
	}
	
	public function visitReferenceMember(Schema\ReferenceMember $member)
	{
		$this->classVariable = $member->getName();
		$this->classMembers[] = $this->classVariable;
		
		$this->classVariableIsReference = true;
		
		$this->acceptStore .= '		$store->visitReferenceProperty("' . $member->getName() . '", ' .
											'"' . $member->getReferencedType() . '", ' . 
											'$this->is' . \ucfirst($member->getName()) . 'Dirty(), ' .
											'$this->get' . \ucfirst($member->getName()) . '());' . "\n";
	}
	public function visitTextMember(Schema\TextMember $member) 
	{
		$this->classVariable = $member->getName();
		$this->classMembers[] = $this->classVariable;
		
		$this->classVariableIsReference = false;
		
		$this->acceptStore .= '		$store->visitTextProperty("' . $member->getName() . '", ' .
											'$this->is' . \ucfirst($member->getName()) . 'Dirty(), ' . 
											'$this->get' . \ucfirst($member->getName()) . '());' . "\n";
	}
	public function visitIntMember(Schema\IntMember $member) 
	{
		$this->classVariable = $member->getName();
		$this->classMembers[] = $this->classVariable;
		
		$this->classVariableIsReference = false;
		
		$this->acceptStore .= '		$store->visitIntProperty("' . $member->getName() . '", ' .
											'$this->is' . \ucfirst($member->getName()) . 'Dirty(), ' . 
											'$this->get' . \ucfirst($member->getName()) . '());' . "\n";
	}
	public function visitFloatMember(Schema\FloatMember $member) 
	{
		$this->classVariable = $member->getName();
		$this->classMembers[] = $this->classVariable;
		
		$this->classVariableIsReference = false;
		
		$this->acceptStore .= '		$store->visitFloatProperty("' . $member->getName() . '", ' .
											'$this->is' . \ucfirst($member->getName()) . 'Dirty(), ' . 
											'$this->get' . \ucfirst($member->getName()) . '());' . "\n";
	}
	public function visitDatetimeMember(Schema\DatetimeMember $member) 
	{
		$this->classVariable = $member->getName();
		$this->classMembers[] = $this->classVariable;
		
		$this->classVariableIsReference = false;
		
		$this->acceptStore .= '		$store->visitDatetimeProperty("' . $member->getName() . '", ' .
											'$this->is' . \ucfirst($member->getName()) . 'Dirty(), ' . 
											'$this->get' . \ucfirst($member->getName()) . '());' . "\n";
	}
	
	public function varDefinitionBefore() {return '';}
	public function varDefinitionAfter() 
	{
		// ucfirst: upper case first letter (it's a php built-in)
		$res  = '	private $is' . \ucfirst($this->classVariable) . 'Dirty =  false;' . "\n";
		$res .= "	\n";
		$res .= '	public function is' . \ucfirst($this->classVariable) . 'Dirty()' . "\n";
		$res .= "	{\n";
		$res .= '		return $this->is' . \ucfirst($this->classVariable) . 'Dirty;' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		$res .= '	public function make' . \ucfirst($this->classVariable) . 'Dirty($value = true)' . "\n";
		$res .= "	{\n";
		$res .= '		$this->is' . \ucfirst($this->classVariable) . 'Dirty = $value;' . "\n";
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
		$res .= '		$this->make' . \ucfirst($this->classVariable) . 'Dirty();' . "\n";
		$res .= '		$this->dirty();' . "\n";
		
		return $res;
	}
	
	public function topOfFile()
	{
		return '';
	}
	
	public function classBody()
	{
		$res  = '	protected function dirty()' . "\n";
		$res .= "	{\n";
		$res .= '		if (!$this->isDirty() && $this->store != null)' . "\n";
		$res .= "		{\n";
		$res .= '			$this->makeDirty(true);' . "\n";
		$res .= '			$this->store->dirty' . \ucfirst($this->className) . '($this);' . "\n";
		$res .= "		}\n";
		$res .= "	}\n";
		$res .= "	\n";
		
		$res .= '	public function makeDirty($value = true)' . "\n";
		$res .= "	{\n";
		$res .= '		$this->dirty = $value;' . "\n";
		$res .= "		\n";
		$res .= '		if ($value == false)' . "\n";
		$res .= "		{\n";
		foreach ($this->classMembers as $member)
		{
			$res .= '			$this->make' . ucfirst($member) . 'Dirty(false);' . "\n";
		}
		$res .= '		' . "\n";
		$res .= "		}\n";
		$res .= "	}\n";
		
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