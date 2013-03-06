<?php

namespace Good\Rolemodel;

class DataType implements Visitable
{
	private $sourceFileName;
	private $name;
	private $dataMembers;
	
	public function __construct($sourceFileName, $name, $dataMembers)
	{
		$this->sourceFileName = $sourceFileName;
		// TODO: make sure name is valid
		$this->name = $name;
		$this->dataMembers = $dataMembers;
	}
	
	public function accept(Visitor $visitor)
	{
		// visit this
		$visitor->visitDataType($this);
		
		// and move the visitor to your children
		for ($i = 0; $i < \count($this->dataMembers); $i++)
		{
			$this->dataMembers[$i]->accept($visitor);
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
		
		for ($i = 0; $i < \count($this->dataMembers); $i++)
		{
			$newElement = $this->dataMembers[$i]->getReferencedTypeIfAny();
			
			if ($newElement != null)
			{
				$res[] = $newElement;
			}
		}
		
		return $res;
	}
}

?>