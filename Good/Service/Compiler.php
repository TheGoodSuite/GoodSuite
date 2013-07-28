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
	
	public function __construct($modifiers, $outputDir)
	{
		$this->outputDir = $outputDir;
		$this->modifiers = $modifiers;
	}
	
	public function visitSchema($schema)
	{
		foreach ($this->modifiers as $modifier)
		{
			$modifier->visitSchema($schema);
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
	
	
	public function visitSchemaEnd()
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
			$modifier->visitSchemaEnd();
		}
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
	
	public function visitReferenceMember($member)
	{
		foreach ($this->modifiers as $modifier)
		{
			$modifier->visitReferenceMember($member);
		}
		
		$varType = $member->getReferencedType();
		$includes[] = $member->getReferencedType();
		
		$this->commitVariable($member, $varType);
	}
	
	public function visitTextMember($member)
	{
		foreach ($this->modifiers as $modifier)
		{
			$modifier->visitTextMember($member);
		}
		
		$varType = 'string';
		
		$this->commitVariable($member, $varType);
	}
	
	public function visitIntMember($member)
	{
		foreach ($this->modifiers as $modifier)
		{
			$modifier->visitIntMember($member);
		}
		
		$varType = 'int';
		
		$this->commitVariable($member, $varType);
	}
	
	public function visitFloatMember($member)
	{
		foreach ($this->modifiers as $modifier)
		{
			$modifier->visitFloatMember($member);
		}
		
		$varType = 'float';
		
		$this->commitVariable($member, $varType);
	}
	
	public function visitDatetimeMember($member)
	{
		foreach ($this->modifiers as $modifier)
		{
			$modifier->visitDatetimeMember($member);
		}
		
		$varType = 'datetime';
		
		$this->commitVariable($member, $varType);
	}
	
	private function commitVariable($member, $varType)
	{
		// Var type is currently unused but might be used when I do typechecking
		// (then again, I might actually do it differently)
		$access = null;
		
		foreach ($member->getAttributes() as $attribute)
		{
			switch ($attribute)
			{
				case 'private':
					// what we call private is actually protected
					// (otherwise it would be useless...)
				case 'protected':
					// but we also allow a user to just use the protected attribute instead
					if ($access != null)
					{
						// TODO: better error handling
						throw new \Exception('Error: More than one attribute specifying access on variable ' . 
								$member->getName() . ' from ' . $this->inputFile . '.');
					}
					$access = 'protected';
				break;
				
				case 'public':
					if ($access != null)
					{
						// TODO: better error handling
						throw new \Exception('Error: More than one attribute specifying access on variable ' . 
								$member->getName() . ' from ' . $this->inputFile . '.');
					}
					$access = 'public';
				break;
				
				default:
				break;
			}
		}
		
		// default access is public
		if ($access == null)
		{
			$access = 'public';
		}
		
		
		foreach ($this->modifiers as $modifier)
		{
			$this->output .= $modifier->varDefinitionBefore();
		}
		
		$this->output .= '	private $' . $member->getName() . ";\n";
		// ucfirst: uper case first letter (php builtin)
		$this->output .= '	private $is' . \ucfirst($member->getName()) . "Null;\n";
		$this->output .= "	\n";
		
		foreach ($this->modifiers as $modifier)
		{
			$this->output .= $modifier->varDefinitionAfter();
		}
		
		// accessors
		
		//getter
		// ucfirst = upper case first letter (it's a php built-in)
		$this->output .= '	' . $access . ' function get' . \ucfirst($member->getName()) . "()\n";
		$this->output .= "	{\n";
		
		foreach ($this->modifiers as $modifier)
		{
			$this->output .= $modifier->getterBegin();
		}
		
		$this->output .= '		return $this->' . $member->getName() . ";\n";
		
		$this->output .= "	}\n";
		$this->output .= "	\n";
		
		//setter
		// ucfirst = upper case first letter (it's a php built-in)
		$this->output .= '	' . $access . ' function set' . \ucfirst($member->getName()) . '($value)' . "\n";
		$this->output .= "	{\n";
		
		foreach ($this->modifiers as $modifier)
		{
			$this->output .= $modifier->setterBegin();
		}
		
		$this->output .= '		$this->' . $member->getName() . ' = $value;' . "\n";
		
		foreach ($this->modifiers as $modifier)
		{
			$this->output .= $modifier->setterEnd();
		}
		
		$this->output .= "	}\n";
		$this->output .= "	\n";
		
		
		// null getter
		// ucfirst: uper case first letter (php builtin)
		$this->output .= '	' . $access . ' function is' . \ucfirst($member->getName()) . 'Null()' . "\n";
		$this->output .= "	{\n";
		
		foreach ($this->modifiers as $modifier)
		{
			$this->output .= $modifier->nullGetterBegin();
		}
		
		$this->output .= '		return $this->is' . \ucfirst($member->getName()) . 'Null;' . "\n";
		$this->output .= "	}\n";
		$this->output .= "	\n";
		
		// null setter
		// ucfirst: uper case first letter (php builtin)
		$this->output .= '	' . $access . ' function make' . \ucfirst($member->getName()) . 'Null($value = true)' . "\n";
		$this->output .= "	{\n";
		
		foreach ($this->modifiers as $modifier)
		{
			$this->output .= $modifier->nullSetterBegin();
		}
		
		$this->output .= '		$this->is' . \ucfirst($member->getName()) . 'Null = $value;' . "\n";
		
		foreach ($this->modifiers as $modifier)
		{
			$this->output .= $modifier->nullSetterEnd();
		}
		
		$this->output .= "	}\n";
		$this->output .= "	\n";
	}
}

?>