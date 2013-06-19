<?php

namespace Good\Rolemodel;

class DataModel implements Visitable
{
	private $dataTypes;
	
	public function __construct($dataTypes)
	{
		// we index the types by their names, so we can easily access them
		$this->dataTypes = array();
		for ($i = 0; $i < \count($dataTypes); $i++)
		{
			$this->dataTypes[$dataTypes[$i]->getName()] = $dataTypes[$i];
		}
		
		// Make sure all referenced dataTypes are present
		foreach ($this->dataTypes as $dataType)
		{
			$references = $dataType->getReferencedTypes();
			
			for ($j = 0; $j < \count($references); $j++)
			{
				if (!isset($this->dataTypes[$references[$j]]))
				{
					// TODO: better error handling
					
					throw new \Exception("Error: Type " . $references[$j] . " was referenced, but not supplied itself.");
				}
			}
		}
	}
		
	public function accept(Visitor $visitor)
	{
		// visit this
		$visitor->visitDataModel($this);
		
		// and move the visitor to your children
		foreach ($this->dataTypes as $dataType)
		{
			$dataType->accept($visitor);
		}
		
		$visitor->visitEnd();
	}
}

?>