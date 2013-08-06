<?php

namespace Good\Manners\Modifier;

use Good\Rolemodel\Schema;

class Storable implements \Good\Service\Modifier
{
	private $className;
	private $classMembers;
	private $classVariable;
	private $classVariableIsReference;
	private $firstClass;
	private $acceptStore;
	
	private $resolver = null;
	private $resolverVisit = null;
	private $extraFiles;
	
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
	
	public function visitSchema(Schema $schema)
	{
		$this->extraFiles = array();
		$this->firstClass = true;
	}
	public function visitSchemaEnd()
	{
		$this->finishDataType();
	}
	
	public function visitDataType(Schema\DataType $dataType)
	{
		if ($this->firstClass)
		{
			$this->firstClass = false;
		}
		else
		{
			$this->finishDataType();
		}
		
		$this->className = $dataType->getName();
		$this->classMembers = array();
		
		$this->acceptStore  = '	public function acceptStore(\\Good\\Manners\\Store $store)' . "\n";
		$this->acceptStore .= "	{\n";
		
		$this->setFromArray  = '	public function setFromArray(array $data)' . "\n";
		$this->setFromArray .= "	{\n";
		$this->setFromArray .= '		foreach ($data as $field => $value)' . "\n";
		$this->setFromArray .= "		{\n";
		$this->setFromArray .= '			switch ($field)' . "\n";
		$this->setFromArray .= "			{\n";
		
		$this->resolver  = "<?php\n";
		$this->resolver .= "\n";
		$this->resolver .= 'class ' . $dataType->getName() . 'Resolver extends \\Good\\Manners\\AbstractResolver' . "\n";
		$this->resolver .= "{\n";
		$this->resolver .= '	public function getType()' . "\n";
		$this->resolver .= "	{\n";
		$this->resolver .= '		return "' . $this->className . '";' . "\n";
		$this->resolver .= "	}\n";
		$this->resolver .= "	\n";
		
		$this->resolverVisit  = '	public function resolverAccept' . 
												'(\\Good\\Manners\\ResolverVisitor $visitor)' . "\n";
		$this->resolverVisit .= "	{\n";
	}
	
	private function finishDataType()
	{
		$this->resolverVisit .= "	}\n";
		$this->resolverVisit .= "	\n";
		
		$this->resolver .= $this->resolverVisit;
		
		$this->resolver .= "}\n";
		$this->resolver .= "\n";
		$this->resolver .= "?>";
		
		$this->extraFiles[$this->className . 'Resolver.php'] = $this->resolver;
	}
	
	public function visitReferenceMember(Schema\ReferenceMember $member)
	{
		$this->classVariable = $member->getName();
		$this->classMembers[] = $this->classVariable;
		
		$this->classVariableIsReference = true;
		
		$this->acceptStore .= '		$store->visitReferenceProperty("' . $member->getName() . '", ' .
											'"' . $member->getReferencedType() . '", ' . 
											'$this->is' . \ucfirst($member->getName()) . 'Dirty, ' .
											'$this->get' . \ucfirst($member->getName()) . '());' . "\n";
		
		$this->setFromArray .= '				case "' . $this->classVariable . '":' . "\n";
		$this->setFromArray .= '					$this->set' . \ucfirst($this->classVariable) . '($value);'. "\n";
		$this->setFromArray .= '					break;' . "\n";
		
		$this->resolver .= '	private $resolved' . \ucfirst($member->getName()) . ' = null;' . "\n"; 
		$this->resolver .= "	\n";
		$this->resolver .= '	public function resolve' . \ucfirst($member->getName()) . '()' . "\n"; 
		$this->resolver .= "	{\n";
		$this->resolver .= '		$this->resolved' . \ucfirst($member->getName()) . ' = ' .
										'new ' . $member->getReferencedType() . 
																'Resolver($this->root);' . "\n";
		$this->resolver .= "		\n";
		$this->resolver .= '		return $this->resolved' . \ucfirst($member->getName()) . ';' . "\n"; 
		$this->resolver .= "	}\n";
		$this->resolver .= "	\n";
		$this->resolver .= '	public function get' . \ucfirst($member->getName()) . '()' . "\n"; 
		$this->resolver .= "	{\n";
		$this->resolver .= '		return $this->resolved' . \ucfirst($member->getName()) . ';' . "\n"; 
		$this->resolver .= "	}\n";
		$this->resolver .= "	\n";
		
		$this->resolverVisit .= '		if ($this->resolved' . \ucfirst($member->getName()) . ' != null)' . "\n";
		$this->resolverVisit .= "		{\n";
		$this->resolverVisit .= '			$visitor->resolverVisitResolvedReferenceProperty("' .
											$member->getName() . '", "' . $member->getReferencedType() . 
											'", ' . '$this->resolved' . \ucfirst($member->getName()) . 
											');' . "\n";
		$this->resolverVisit .= "		}\n";
		$this->resolverVisit .= '		else' . "\n";
		$this->resolverVisit .= "		{\n";
		$this->resolverVisit .= '			$visitor->resolverVisitUnresolvedReferenceProperty(' . 
											'"' . $member->getName() . '");' . "\n";
		$this->resolverVisit .= "		}\n";
	}
	public function visitTextMember(Schema\TextMember $member) 
	{
		$this->classVariable = $member->getName();
		$this->classMembers[] = $this->classVariable;
		
		$this->classVariableIsReference = false;
		
		$this->acceptStore .= '		$store->visitTextProperty("' . $member->getName() . '", ' .
											'$this->is' . \ucfirst($member->getName()) . 'Dirty, ' . 
											'$this->get' . \ucfirst($member->getName()) . '());' . "\n";
		
		$this->setFromArray .= '				case "' . $this->classVariable . '":' . "\n";
		$this->setFromArray .= '					$this->set' . \ucfirst($this->classVariable) . '($value);'. "\n";
		$this->setFromArray .= '					break;' . "\n";
		
		$this->visitNonReference($member);
	}
	public function visitIntMember(Schema\IntMember $member) 
	{
		$this->classVariable = $member->getName();
		$this->classMembers[] = $this->classVariable;
		
		$this->classVariableIsReference = false;
		
		$this->acceptStore .= '		$store->visitIntProperty("' . $member->getName() . '", ' .
											'$this->is' . \ucfirst($member->getName()) . 'Dirty, ' . 
											'$this->get' . \ucfirst($member->getName()) . '());' . "\n";
		
		$this->setFromArray .= '				case "' . $this->classVariable . '":' . "\n";
		$this->setFromArray .= '					$this->set' . \ucfirst($this->classVariable) . '($value);'. "\n";
		$this->setFromArray .= '					break;' . "\n";
		
		$this->visitNonReference($member);
	}
	public function visitFloatMember(Schema\FloatMember $member) 
	{
		$this->classVariable = $member->getName();
		$this->classMembers[] = $this->classVariable;
		
		$this->classVariableIsReference = false;
		
		$this->acceptStore .= '		$store->visitFloatProperty("' . $member->getName() . '", ' .
											'$this->is' . \ucfirst($member->getName()) . 'Dirty, ' . 
											'$this->get' . \ucfirst($member->getName()) . '());' . "\n";
		
		$this->setFromArray .= '				case "' . $this->classVariable . '":' . "\n";
		$this->setFromArray .= '					$this->set' . \ucfirst($this->classVariable) . '($value);'. "\n";
		$this->setFromArray .= '					break;' . "\n";
		
		$this->visitNonReference($member);
	}
	public function visitDatetimeMember(Schema\DatetimeMember $member) 
	{
		$this->classVariable = $member->getName();
		$this->classMembers[] = $this->classVariable;
		
		$this->classVariableIsReference = false;
		
		$this->acceptStore .= '		$store->visitDatetimeProperty("' . $member->getName() . '", ' .
											'$this->is' . \ucfirst($member->getName()) . 'Dirty, ' . 
											'$this->get' . \ucfirst($member->getName()) . '());' . "\n";
		
		$this->setFromArray .= '				case "' . $this->classVariable . '":' . "\n";
		$this->setFromArray .= '					if ($value === null || $value instanceof \DateTime)' . "\n";
		$this->setFromArray .= "					{\n";
		$this->setFromArray .= '						$this->set' . \ucfirst($this->classVariable) . '($value);'. "\n";
		$this->setFromArray .= "					}\n";
		$this->setFromArray .= '					else' . "\n";
		$this->setFromArray .= "					{\n";
		$this->setFromArray .= '						$this->set' . \ucfirst($this->classVariable) . '(new DateTime($value));'. "\n";
		$this->setFromArray .= "					}\n";
		$this->setFromArray .= '					break;' . "\n";
		
		$this->visitNonReference($member);
	}
	
	private function visitNonReference($member)
	{
		$this->resolver .= '	private $orderNumber' . \ucfirst($member->getName()) . ' = -1;' . "\n";
		$this->resolver .= '	private $orderDirection' . \ucfirst($member->getName()) . ' = -1;' . "\n";
		$this->resolver .= "	\n";
		$this->resolver .= '	public function orderBy' . \ucfirst($member->getName()) . 'Asc()' . "\n";
		$this->resolver .= "	{\n";
		$this->resolver .= '		$this->orderNumber' . \ucfirst($member->getName()) .
														' = $this->drawOrderTicket();' . "\n";
		$this->resolver .= '		$this->orderDirection' . \ucfirst($member->getName()) . 
														' = self::ORDER_DIRECTION_ASC;' . "\n";
		$this->resolver .= "	}\n";
		$this->resolver .= "	\n";
		$this->resolver .= '	public function orderBy' . \ucfirst($member->getName()) . 'Desc()' . "\n";
		$this->resolver .= "	{\n";
		$this->resolver .= '		$this->orderNumber' . \ucfirst($member->getName()) .
														' = $this->drawOrderTicket();' . "\n";
		$this->resolver .= '		$this->orderDirection' . \ucfirst($member->getName()) . 
														' = self::ORDER_DIRECTION_DESC;' . "\n";
		$this->resolver .= "	}\n";
		$this->resolver .= "	\n";
		
		$this->resolverVisit .= '		$visitor->resolverVisitNonReferenceProperty("' .
															$member->getName() . '");' . "\n";
		$this->resolverVisit .= '		if ($this->orderNumber' . \ucfirst($member->getName()) . ' != -1)' . "\n";
		$this->resolverVisit .= "		{\n";
		$this->resolverVisit .= '			if ($this->orderDirection' . \ucfirst($member->getName()) . 
														'== self::ORDER_DIRECTION_ASC)' . "\n";
		$this->resolverVisit .= "			{\n";
		$this->resolverVisit .= '				$visitor->resolverVisitOrderAsc($this->orderNumber' 
													. \ucfirst($member->getName()) . ', "'
													. $member->getName() . '");' . "\n";
		$this->resolverVisit .= "			}\n";
		$this->resolverVisit .= '			else' . "\n";
		$this->resolverVisit .= "			{\n";
		$this->resolverVisit .= '				$visitor->resolverVisitOrderDesc($this->orderNumber' 
													. \ucfirst($member->getName()) . ', "'
													. $member->getName() . '");' . "\n";
		$this->resolverVisit .= "			}\n";
		$this->resolverVisit .= "		}\n";
	}
	
	public function varDefinitionBefore() {return '';}
	public function varDefinitionAfter() 
	{
		// ucfirst: upper case first letter (it's a php built-in)
		$res  = '	private $is' . \ucfirst($this->classVariable) . 'Dirty =  false;' . "\n";
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
		$res .= '			$this->store->dirtyStorable($this);' . "\n";
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
		$res .= '	public function getType()' . "\n";
		$res .= "	{\n";
		$res .= '		return "' . $this->className . '";' . "\n";
		$res .= "	}\n";
		$res .= "	\n";
		
		$this->acceptStore .= "	}\n";
		$this->acceptStore .= "	\n";
		
		$res .= $this->acceptStore;
		
		$this->setFromArray .= '				default:' . "\n";
		$this->setFromArray .= '					throw new Exception("Unrecognised field in setFromArray array.");' . "\n";
		$this->setFromArray .= '					break;' . "\n";
		$this->setFromArray .= "			}\n";
		$this->setFromArray .= "		}\n";
		$this->setFromArray .= "	}\n";
		
		$res .= $this->setFromArray;
		
		return $res;
	}
	public function bottomOfFile() {return '';}
	
	public function extraFiles()
	{
		return $this->extraFiles;
	}
}

?>