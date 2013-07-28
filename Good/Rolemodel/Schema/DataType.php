<?php

namespace Good\Rolemodel\Schema;

use Good\Rolemodel\Visitor;

class DataType
{
	private $sourceFileName;
	private $name;
	private $members;
	
	public function __construct($sourceFileName, $name, $members)
	{
		$this->sourceFileName = $sourceFileName;
		// TODO: make sure name is valid
		$this->name = $name;
		$this->members = $members;
	}
	
	public function accept(Visitor $visitor)
	{
		// visit this
		$visitor->visitDataType($this);
		
		// and move the visitor to your children
		for ($i = 0; $i < \count($this->members); $i++)
		{
			$this->members[$i]->accept($visitor);
		}
	}
	
	public function getSourceFileName()
	{
		return $this->sourceFileName;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getReferencedTypes()
	{
		$res = array();
		
		for ($i = 0; $i < \count($this->members); $i++)
		{
			$newElement = $this->members[$i]->getReferencedTypeIfAny();
			
			if ($newElement != null)
			{
				$res[] = $newElement;
			}
		}
		
		return $res;
	}
}

?>