<?php

namespace Good\Service;

class Compiler implements \Good\Rolemodel\Visitor
{
	// TODO: prevent namespace collisions between things between
	//       modifiers and generated variables / accessors

	// Compiler level data
	private $outputDir;
	private $modifiers;
	
	// file level data
	private $inputFile = null;
	private $outputFile = null;
	private $output = null;
	private $includes = null;
	private $className = null;
	
	// variable level data
	private $varName = null;
	private $access = null;
	
	public function __construct($modifiers, $outputDir)
	{
		$this->outputDir = $outputDir;
		$this->modifiers = $modifiers;
	}
	
	public function visitDataModel($dataModel)
	{
		foreach ($this->modifiers as $modifier)
		{
			$modifier->visitDataModel($dataModel);
		}
		
		// TODO: prevent namespace and filename collisions here
		// Build the base class 
		$output  = "<?php\n";
		$output .= "\n";
		foreach ($this->modifiers as $modifier)
		{
			$output .= $modifier->baseClassTopOfFile();
		}
		$output .= "abstract class GeneratedBaseClass";
		
		$first = true;
		foreach ($this->modifiers as $modifier)
		{
			foreach ($modifier->implementingInterfaces() as $interface)
			{
				if ($first)
				{
					$output .= ' implements ' . $interface;
					$first = false;
				}
				else
				{
					$output .= ', ' . $interface;
				}
			}
		}
		
		$output .= "\n";
		
		$output .= "{\n";
		$output .= "	public function __construct()\n";
		$output .= "	{\n";
			foreach ($this->modifiers as $modifier)
			{
				$output .= $modifier->baseClassConstructor();
			}
		$output .= "	}\n";
		$output .= "\n";
		foreach ($this->modifiers as $modifier)
		{
			$output .= $modifier->baseClassBody();
		}
		$output .= "}\n";
		$output .= "\n";
		$output .= '?>';
		
		\file_put_contents($this->outputDir . 'GeneratedBaseClass.php', $output);
	}
	
	public function visitDataType($dataType)
	{
		if ($this->output != null)
		{
			$this->saveOutput();
		}
		
		foreach ($this->modifiers as $modifier)
		{
			$modifier->visitDataType($dataType);
		}
		
		$this->className = $dataType->getName();
		
		$this->includes = array();
		
		// ucfirst: make first letter upper case
		$this->output = 'abstract class Base' . \ucfirst($dataType->getName())
													. " extends GeneratedBaseClass\n";
		
		$this->output .= "{\n";
		$this->inputFile = $dataType->getSourceFileName();
		// TODO: make following line independant of execution path at any time
		//       and escape some stuff
		// Note: This was previously based on the input file namespace
		//       But I changed it to dataType name instead
		$this->outputFile = $this->outputDir . 'Base' . \ucfirst($dataType->getName()) . '.datatype.php';
	}
	
	private function saveOutput()
	{
		foreach ($this->modifiers as $modifier)
		{
			$this->output .= $modifier->classBody();
		}
		
		$this->output .= "}\n";
		
		// neatly start the file
		$top  = "<?php\n";
		$top .= "\n";
		
		$top .= "include_once 'GeneratedBaseClass.php';\n";
		$top .= "\n";
		
		// TODO: fix includes
		//       (we can do without for now, as we don't force the type yet,
		//		  don't actually use the includes yet)
		// includes
		//foreach ($includes as $include)
		//{
		//
		//}
		
		foreach ($this->modifiers as $modifier)
		{
			$top .= $modifier->topOfFile();
		}
		
		$this->output = $top . $this->output;
		
		foreach ($this->modifiers as $modifier)
		{
			$this->output .= $modifier->bottomOfFile();
		}
		
		// close the file off
		$this->output .= "\n";
		$this->output .= "?>";
		
		
		$contents  = '<?php' . "\n";
		$contents .= "\n";
		$contents .= 'class ' . $this->className . ' extends Base' . \ucfirst($this->className) . "\n";
		$contents .= "{\n";
		$contents .= "}\n";
		$contents .= "\n";
		$contents .= '?>' . "\n";
		
		\file_put_contents($this->outputFile, $this->output);
		
		// TODO: make following line independant of execution path at any time
		//       and escape some stuff
		$file = $this->outputDir . $this->className . '.datatype.php';
		\file_put_contents($file, $contents);
	}
	
	public function visitDataMember($dataMember)
	{
		foreach ($this->modifiers as $modifier)
		{
			$modifier->visitDataMember($dataMember);
		}
		
		$this->varName = $dataMember->getName();
		
		$this->access = null;
		
		foreach ($dataMember->getAttributes() as $attribute)
		{
			switch ($attribute)
			{
				case 'private':
					// what we call private is actually protected
					// (otherwise it would be useless...)
				case 'protected':
					// but we also allow a user to just use the protected attribute instead
					if ($this->access != null)
					{
						// TODO: better error handling
						throw new \Exception('Error: More than one attribute specifying access on variable ' . 
								$this->varName . ' from ' . $this->inputFile . '.');
					}
					$this->access = 'protected';
				break;
				
				case 'private':
					if ($this->access != null)
					{
						// TODO: better error handling
						throw new \Exception('Error: More than one attribute specifying access on variable ' . 
								$this->varName . ' from ' . $this->inputFile . '.');
					}
					$this->access = 'private';
				break;
				
				default:
				break;
			}
		}
		
		// default access is public
		if ($this->access == null)
		{
			$this->access = 'public';
		}
	}
	
	public function visitTypeReference($type)
	{
		foreach ($this->modifiers as $modifier)
		{
			$modifier->visitTypeReference($type);
		}
		
		$varType = $type->getReferencedType();
		$includes[] = $type->getReferencedType();
		
		$this->commitVariable();
	}
	
	public function visitTypePrimitiveText($type)
	{
		foreach ($this->modifiers as $modifier)
		{
			$modifier->visitTypePrimitiveText($type);
		}
		
		$varType = 'string';
		
		$this->commitVariable();
	}
	
	public function visitTypePrimitiveInt($type)
	{
		foreach ($this->modifiers as $modifier)
		{
			$modifier->visitTypePrimitiveInt($type);
		}
		
		$varType = 'int';
		
		$this->commitVariable();
	}
	
	public function visitTypePrimitiveFloat($type)
	{
		foreach ($this->modifiers as $modifier)
		{
			$modifier->visitTypePrimitiveFloat($type);
		}
		
		$varType = 'float';
		
		$this->commitVariable();
	}
	
	public function visitTypePrimitiveDatetime($type)
	{
		foreach ($this->modifiers as $modifier)
		{
			$modifier->visitTypePrimitiveDatetime($type);
		}
		
		$varType = 'datetime';
		
		$this->commitVariable();
	}
	
	private function commitVariable()
	{
		foreach ($this->modifiers as $modifier)
		{
			$this->output .= $modifier->varDefinitionBefore();
		}
		
		$this->output .= '	private $' . $this->varName . ";\n";
		// ucfirst: uper case first letter (php builtin)
		$this->output .= '	private $is' . \ucfirst($this->varName) . "Null;\n";
		$this->output .= "	\n";
		
		foreach ($this->modifiers as $modifier)
		{
			$this->output .= $modifier->varDefinitionAfter();
		}
		
		// accessors
		
		//getter
		// ucfirst = upper case first letter (it's a php built-in)
		$this->output .= '	' . $this->access . ' function get' . \ucfirst($this->varName) . "()\n";
		$this->output .= "	{\n";
		
		foreach ($this->modifiers as $modifier)
		{
			$this->output .= $modifier->getterBegin();
		}
		
		$this->output .= '		return $this->' . $this->varName . ";\n";
		
		$this->output .= "	}\n";
		$this->output .= "	\n";
		
		//setter
		// ucfirst = upper case first letter (it's a php built-in)
		$this->output .= '	' . $this->access . ' function set' . \ucfirst($this->varName) . '($value)' . "\n";
		$this->output .= "	{\n";
		
		foreach ($this->modifiers as $modifier)
		{
			$this->output .= $modifier->setterBegin();
		}
		
		$this->output .= '		$this->' . $this->varName . ' = $value;' . "\n";
		
		foreach ($this->modifiers as $modifier)
		{
			$this->output .= $modifier->setterEnd();
		}
		
		$this->output .= "	}\n";
		$this->output .= "	\n";
		
		
		// null getter
		// ucfirst: uper case first letter (php builtin)
		$this->output .= '	' . $this->access . ' function is' . \ucfirst($this->varName) . 'Null()' . "\n";
		$this->output .= "	{\n";
		
		foreach ($this->modifiers as $modifier)
		{
			$this->output .= $modifier->nullGetterBegin();
		}
		
		$this->output .= '		return $this->is' . \ucfirst($this->varName) . 'Null;' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		
		// null setter
		// ucfirst: uper case first letter (php builtin)
		$this->output .= '	' . $this->access . ' function make' . \ucfirst($this->varName) . 'Null($value = true)' . "\n";
		$this->output .= "	{\n";
		
		foreach ($this->modifiers as $modifier)
		{
			$this->output .= $modifier->nullSetterBegin();
		}
		
		$this->output .= '		$this->is' . \ucfirst($this->varName) . 'Null = $value;' . "\n";
		
		foreach ($this->modifiers as $modifier)
		{
			$this->output .= $modifier->nullSetterEnd();
		}
		
		$this->output .= "	}\n";
		$this->output .= "	\n";
		
	}
	
	
	public function visitEnd()
	{
		if ($this->output != null)
		{
			$this->saveOutput();
		}
		
		$this->output = null;
		
		foreach ($this->modifiers as $modifier)
		{
			foreach($modifier->extraFiles() as $filename => $contents)
			{
				\file_put_contents($this->outputDir . $filename, $contents);
			}
		}
		
		foreach ($this->modifiers as $modifier)
		{
			$modifier->visitEnd();
		}
	}
}

?>