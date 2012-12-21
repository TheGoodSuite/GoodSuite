<?php

include_once 'Visitable.php';
include_once 'TypeReference.php';
include_once 'PrimitiveFactory.php';

class GoodRolemodelDataMember implements GoodRolemodelVisitable
{
	private $attributes;
	private $type;
	private $name;
	
	private static $knownAttributes = array('server_only', 'private', 'public');
	
	public function __construct($attributes, $type, $name)
	{
		// Attributes
	
		$this->attributes = $attributes;
		
		// check for unknown attributes
		for ($i = 0; $i < count($attributes); $i++)
		{
			if (!in_array($attributes[$i], self::$knownAttributes))
			{
				// TODO: add a real warning
				
				// WARNING: unknown attribute
			}
		}
		
		// Type
		
		if (substr($type, 0, 1) == '"' && substr($type, -1) == '"')
		{
			$this->type = new GoodRolemodelTypeReference(substr($type, 1, -1));
		}
		else
		{
			$this->type = GoodRolemodelPrimitiveFactory::makePrimitive($type);
		}
		
		// Name
		$this->name = $name;
	}
	
	public function accept(GoodRolemodelVisitor $visitor)
	{
		// visit this
		$visitor->visitDataMember($this);
		
		// move the visitor to your child
		$this->type->accept($visitor);
	}
	
	public function getReferencedTypeIfAny()
	{
		return $this->type->getReferencedTypeIfAny();
	}
}

?>