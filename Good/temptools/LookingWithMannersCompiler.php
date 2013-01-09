<?php

// This is a temporary script that fixes the incompatibility between the
// GoodLooking way of accessing variables with the GoodMannersStorables
// Though this should be fixed on a higher level, this uses the temporary
// solution of creating a script that rewrites the GoodMannersStorables
// content into a format that works with GoodLooking

// input $model - GoodRoleModelDataModel
//
// task: writes a script "LookingWithManners.php", which alows your Storables
//       to be rewritten to arrays which GoodLooking can handle
//
//       not termin

// **Generated script**
//
// function parse<Type>($storable)
//   <Type>: Storable type
//   $storable: A GoodMannersStorable of type <Type> that should be rewritten
//              as an array.
//   returns: An array that contains all the information from $storable
//
// function parse<Type>Collection($collection)
//   <Type>: Storable type
//   $collection: A Collection of the Collection type for <Type>
//   returns: An array with all the Storables from $collection. The array will
//            non-sparse, thus beginning at 0 and sequentially numbered from
//            there. The order of the objects will be the same as in
//            $collection (thus allowing for proper usages of ordering on
//            the collection.
//
// NOTE: If a given Storable contains a circular reference, this script will
//       not terminate

include_once dirname(__FILE__) . '/../Rolemodel/Visitor.php';

class LookingWithMannersCompiler implements GoodRolemodelVisitor
{
	private $outputDir;
	private $output = null;
	private $varName = null;
	private $firstDataType = true;
	private $isPublic = false;
	
	public function compile($model, $outputDir)
	{
		$this->outputDir = $outputDir;
		
		$model->accept($this);
	}
	
	public function visitDataModel($dataModel)
	{
		$this->output  = "<?php\n";
		$this->output .= "\n";
	}
	
	public function visitDataType($dataType)
	{
		if ($this->firstDataType)
		{
			$this->firstDataType = false;
		}
		else
		{
			$this->finishDataType();
		}
		
		$className = $dataType->getName();
		
		$this->output .= 'function parse' . ucfirst($className) . 'Collection($collection)' . "\n";
		$this->output .= "{\n";
		$this->output .= '	$arr = array();' . "\n";
		$this->output .= "	\n";
		$this->output .= '	while ($obj = $collection->getNext())' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		$arr[] = parse' . ucfirst($className) . '($obj);' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		$this->output .= '	return $arr;' . "\n";
		$this->output .= "}\n";
		$this->output .= "\n";
		$this->output .= 'function parse' . ucfirst($className) . '($obj)' . "\n";
		$this->output .= "{\n";
		$this->output .= '	if ($obj == null)' . "\n";
		$this->output .= "	{\n";
		$this->output .= '		return null;' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		$this->output .= '	$arr = array();' . "\n";
		$this->output .= "	\n";
		$this->output .= '	$arr["id"] = $obj->getId();' . "\n";
	}
	
	private function finishDataType()
	{
		$this->output .= "	\n";
		$this->output .= '	return $arr;' . "\n";
		$this->output .= "}\n";
		$this->output .= "\n";
	}
	
	public function visitDataMember($dataMember)
	{
		$this->varName = $dataMember->getName();
		
		
		// This is a pretty ugly hack and it should perhaps be fixed
		// (move the visibility to the datamodel from the compiler)
		// but since this whole script is in fact an ugly fix,
		// I allowed it for now.
		$this->isPublic = !(in_array('private', $dataMember->getAttributes()) || 
							in_array('protected', $dataMember->getAttributes()));
	}
	
	public function visitTypeReference($type)
	{
		if ($this->isPublic)
		{
			$this->output .= '	$arr["' . $this->varName . '"] = parse' . 
									ucfirst($type->getReferencedType()) .
									'($obj->get' . ucfirst($this->varName) . '());' . "\n";
		}
	}
	
	public function visitTypePrimitiveText($type)
	{
		$this->visitNonReference($type);
	}
	
	public function visitTypePrimitiveInt($type)
	{
		$this->visitNonReference($type);
	}
	
	public function visitTypePrimitiveFloat($type)
	{
		$this->visitNonReference($type);
	}
	
	private function visitNonReference()
	{
		if ($this->isPublic)
		{
			$this->output .= '	$arr["' . $this->varName . '"] = $obj->get' . 
												ucfirst($this->varName) . '();' . "\n";
		}
	}
	
	
	public function visitEnd()
	{
		if (!$this->firstDataType)
		{
			$this->finishDataType();
		}
		
		// close the file off
		$this->output .= "\n";
		$this->output .= "?>";
		
		file_put_contents($this->outputDir . 'LookingWithManners.php', $this->output);
	}
}