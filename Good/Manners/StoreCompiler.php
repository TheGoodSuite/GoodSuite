<?php

namespace Good\Manners;

use Good\Rolemodel\Schema;

class StoreCompiler implements \Good\Rolemodel\Visitor
{
	// Compiler level data
	private $outputDir;
	private $dataTypes = array();
	private $output = null;
	private $firstDateType = true;
	private $dataType = null;
	
	private $resolver = null;
	private $resolverVisit = null;
	
	public function __construct($outputDir)
	{
		$this->outputDir = $outputDir;
	}
	
	public function visitSchema(Schema $schema)
	{
		// Start off the class 
		$this->output  = "abstract class GoodMannersStore implements \\Good\\Manners\\Store \n";
		$this->output .= "{\n";
		$this->output .= '	private $validationToken;' . "\n";
		$this->output .= "	\n";
		$this->output .= "	public function __construct()\n";
		$this->output .= "	{\n";
		$this->output .= '		$this->validationToken = new \\Good\\Manners\\ValidationToken();' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		$this->output .= "	public function __destruct()\n";
		$this->output .= "	{\n";
		$this->output .= '		$this->flush();' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		$this->output .= '	abstract public function visitReferenceProperty($name, ' .
								'$datatypeName, $dirty, \\Good\\Manners\\Storable $value = null);' . "\n";
		$this->output .= '	abstract public function visitTextProperty($name, $dirty, ' .
																	'$value);' . "\n";
		$this->output .= '	abstract public function visitIntProperty($name, $dirty, ' .
																	'$value);' . "\n";
		$this->output .= '	abstract public function visitFloatProperty($name, $dirty, ' .
																	'$value);' . "\n";
		$this->output .= '	abstract public function visitDatetimeProperty($name, $dirty, ' .
																	'$value);' . "\n";
		$this->output .= "	\n";
		$this->output .= '	protected function invalidate()' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		$this->validationToken->invalidate();' . "\n";
		$this->output .= '		$this->validationToken = new \\Good\\Manners\\ValidationToken();' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
	}
	
	public function visitSchemaEnd()
	{
		$this->finishDataType();
	
		$this->output .= '	private $flushes = 0;' . "\n";
		$this->output .= "	\n";
		$this->output .= '	public function flush()' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		$this->flushes++;' . "\n";
		
		$new = '';
		$modified = '';
		$deleted = '';
		
		foreach ($this->dataTypes as $type)
		{
			$this->output .= '		// Sort all the ' . $type . ' objects' . "\n";
			$this->output .= '		$deleted' . ucfirst($type) . 's = array();' . "\n";
			$this->output .= '		$modified' . ucfirst($type) . 's = array();' . "\n";
			$this->output .= '		$new' . ucfirst($type) . 's = array();' . "\n";
			$this->output .= "		\n";
			// ucfirst: Make first letter uppercase (it's a part of php)
			$this->output .= '		foreach ($this->dirty' . \ucfirst($type) . 's as $dirty)' . "\n";
			$this->output .= "		{\n";
			$this->output .= '			if ($dirty->isDeleted() && !$dirty->isNew())' . "\n";
			$this->output .= "			{\n";
			$this->output .= '				$deleted' . ucfirst($type) . 's[] = $dirty;' . "\n";
			$this->output .= "			}\n";
			$this->output .= '			else if ($dirty->isNew() && !$dirty->isDeleted())' . "\n";
			$this->output .= "			{\n";
			$this->output .= '				$new' . ucfirst($type) . 's[] = $dirty;' . "\n";
			$this->output .= "			}\n";
			$this->output .= '			else if (!$dirty->isNew())' . "\n";
			$this->output .= "			{\n";
			$this->output .= '				$modified' . ucfirst($type) . 's[] = $dirty;' . "\n";
			$this->output .= "			}\n";
			$this->output .= "		}\n";
			$this->output .= "		\n";
			$this->output .= '		$this->dirty' . \ucfirst($type) . 's = array();' . "\n";
			$this->output .= "		\n";
			
			$new .= '		if (count($new' . ucfirst($type) . 's) > 0)' . "\n";
			$new .= "		{\n";
			$new .= '			$this->saveNew' . ucfirst($type) . 's($new' . ucfirst($type) . 's);' . "\n";
			$new .= "		}\n";
			$new .= "		\n";
			
			$modified .= '		if (count($modified' . ucfirst($type) . 's) > 0)' . "\n";
			$modified .= "		{\n";
			$modified .= '			$this->save' . \ucfirst($type) . 'Modifications($modified' . ucfirst($type) . 's);' . "\n";
			$modified .= "		}\n";
			$modified .= "		\n";
			
			$deleted .= '		if (count($deleted' . ucfirst($type) . 's) > 0)' . "\n";
			$deleted .= "		{\n";
			$deleted .= '			$this->save' . \ucfirst($type) . 'Deletions($deleted' . ucfirst($type) . 's);' . "\n";
			$deleted .= "		}\n";
			$deleted .= "		\n";
		}
		
		$this->output .= $new;
		$this->output .= $modified;
		$this->output .= $deleted;
		
		$this->output .= '		$this->flushes--;' . "\n";
		$this->output .= "		\n";
		$this->output .= '		if ($this->flushes == 0 && $this->reflush == true)' . "\n";
		$this->output .= "		{\n";
		$this->output .= '			$this->reflush = false;' . "\n";
		$this->output .= '			$this->flush();' . "\n";
		$this->output .= "		}\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		$this->output .= '	private $reflush = false;' . "\n";
		$this->output .= "	\n";
		$this->output .= '	public function reflush()' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		if ($this->flushes == 0)' . "\n";
		$this->output .= "		{\n";
		$this->output .= '			$this->reflush = false;' . "\n";
		$this->output .= '			$this->flush();' . "\n";
		$this->output .= "		}\n";
		$this->output .= '		else' . "\n";
		$this->output .= "		{\n";
		$this->output .= '			$this->reflush = true;' . "\n";
		$this->output .= "		}\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		
		
		// neatly start the file
		$top  = "<?php\n";
		$top .= "\n";
		
		foreach ($this->dataTypes as $className)
		{
			// TODO: Either make this work in some way, or remove it.
			//$top .= "require_once '" . $className . ".datatype.php';\n";
		}
		
		$top .= "\n";
		
		$this->output = $top . $this->output;
		
		// close the file off
		$this->output .= "}\n";
		$this->output .= "\n";
		$this->output .= "?>";
		
		file_put_contents($this->outputDir . 'Store.php', $this->output);
	}
	
	public function visitDataType(Schema\DataType $dataType)
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
		$this->dataTypes[] = $name;
		
		// ucfirst: upper case first (php builtin)
		$this->output .= '	abstract protected function doModifyAny' . \ucfirst($name) . 
							'(\\Good\\Manners\\Condition $condition, ' . $name . ' $modifications);' . "\n";
		$this->output .= '	abstract protected function doGet' . \ucfirst($name) .
							'Collection(\\Good\\Manners\\Condition $condition, ' . $name . 
															'Resolver $resolver);' . "\n";
		$this->output .= "	\n";
		$this->output .= '	abstract protected function saveNew' . \ucfirst($name) . 
																's(array $entries);' . "\n";
		$this->output .= '	abstract protected function save' . \ucfirst($name) . 
															'Modifications(array $entries);' . "\n";
		$this->output .= '	abstract protected function save' . \ucfirst($name) . 
															'Deletions(array $entries);' . "\n";
		$this->output .= "	\n";
		
		$this->output .= '	private $dirty' . \ucfirst($name) . 's = array();' . "\n";
		$this->output .= "	\n";
		$this->output .= '	public function dirty' . \ucfirst($name) . 
												'(' . $name . ' $storable)' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		$this->dirty' . \ucfirst($name) . 's[] = $storable;' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		$this->output .= '	public function insert' . \ucfirst($name) . 
												'(' . $name . ' $storable)' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		$storable->setStore($this);' . "\n";
		$this->output .= '		$storable->setValidationToken($this->validationToken);' . "\n";
		$this->output .= "		\n";
		$this->output .= '		$this->dirty' . \ucfirst($name) . 's[] = $storable;' . "\n";
		$this->output .= "	}\n";
		$this->output .= "\n";
		$this->output .= '	public function modifyAny' . \ucfirst($name) .'(\\Good\\Manners\\Condition ' .
													'$condition, ' . $name . ' $modifications)' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		$this->flush();' . "\n";
		$this->output .= '		$this->invalidate();' . "\n";
		$this->output .= "		\n";
		$this->output .= '		$this->doModifyAny' . \ucfirst($name) .'($condition, $modifications);' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		$this->output .= '	public function get' . \ucfirst($name) . 'Collection(\\Good\\Manners\\Condition ' .
													 '$condition, ' . $name . 'Resolver $resolver)' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		$this->flush();' . "\n";
		$this->output .= '		return $this->doGet' . \ucfirst($name) . 
													'Collection($condition, $resolver);' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		
		$this->resolver  = "<?php\n";
		$this->resolver .= "\n";
		$this->resolver .= 'class ' . $name . 'Resolver extends \\Good\\Manners\\AbstractResolver' . "\n";
		$this->resolver .= "{\n";
		
		$this->resolverVisit  = '	public function resolverAccept' . 
												'(\\Good\\Manners\\ResolverVisitor $visitor)' . "\n";
		$this->resolverVisit .= "	{\n";
	}
	
	public function visitReferenceMember(Schema\ReferenceMember $member)
	{
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
		$this->visitNonReference($member);
	}
	public function visitIntMember(Schema\IntMember $member)
	{
		$this->visitNonReference($member);
	}
	public function visitFloatMember(Schema\FloatMember $member)
	{
		$this->visitNonReference($member);
	}
	public function visitDatetimeMember(Schema\DatetimeMember $member)
	{
		$this->visitNonReference($member);
	}
	
	private function visitNonReference(Schema\PrimitiveMember $member)
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
	
	private function finishDataType()
	{
		$this->resolverVisit .= "	}\n";
		$this->resolverVisit .= "	\n";
		
		$this->resolver .= $this->resolverVisit;
		
		$this->resolver .= "}\n";
		$this->resolver .= "\n";
		$this->resolver .= "?>";
		
		\file_put_contents($this->outputDir . $this->dataType . 'Resolver.php', $this->resolver);
	}
}

?>