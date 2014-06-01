<?php

namespace Good\Service;

use Good\Rolemodel\Schema;

class Compiler implements \Good\Rolemodel\SchemaVisitor
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
    private $getters = null;
    private $setters = null;
    
    public function __construct($modifiers, $outputDir)
    {
        $this->outputDir = $outputDir;
        $this->modifiers = $modifiers;
    }
    
    public function visitSchema(Schema $schema)
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
        $output .= "    public function __construct()\n";
        $output .= "    {\n";
            foreach ($this->modifiers as $modifier)
            {
                $output .= $modifier->baseClassConstructor();
            }
        $output .= "    }\n";
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
            $modifier->visitSchemaEnd();
        }
        
        foreach ($this->modifiers as $modifier)
        {
            foreach($modifier->extraFiles() as $filename => $contents)
            {
                \file_put_contents($this->outputDir . $filename, $contents);
            }
        }
    }
    
    public function visitDataType(Schema\DataType $dataType)
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
        
        $this->output = 'class ' . $dataType->getName() . " extends GeneratedBaseClass\n";
        
        $this->output .= "{\n";
        $this->inputFile = $dataType->getSourceFileName();
        // TODO: make following line independant of execution path at any time
        //       and escape some stuff
        // Note: This was previously based on the input file namespace
        //       But I changed it to dataType name instead
        $this->outputFile = $this->outputDir . $dataType->getName() . '.datatype.php';
        
        $this->getters  = '    public function __get($property)' . "\n";
        $this->getters .= "    {\n";
        $this->getters .= '        switch ($property)' . "\n";
        $this->getters .= "        {\n";
        
        $this->setters  = '    public function __set($property, $value)' . "\n";
        $this->setters .= "    {\n";
        $this->setters .= '        switch ($property)' . "\n";
        $this->setters .= "        {\n";
    }
    
    private function saveOutput()
    {
        $this->getters .= '            default:' . "\n";
        $this->getters .= '                throw new \Exception("Unknown or non-public property");' . "\n";
        $this->getters .= "        }\n";
        $this->getters .= "    }\n";
        
        $this->setters .= '            default:' . "\n";
        $this->setters .= '                throw new \Exception("Unknown or non-public property");' . "\n";
        $this->setters .= "        }\n";
        $this->setters .= "    }\n";
        
        $this->output .= $this->getters;
        
        $this->output .= $this->setters;
        
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
        //          don't actually use the includes yet)
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
        
        \file_put_contents($this->outputFile, $this->output);
    }
    
    public function visitReferenceMember(Schema\ReferenceMember $member)
    {
        foreach ($this->modifiers as $modifier)
        {
            $modifier->visitReferenceMember($member);
        }
        
        $varType = $member->getReferencedType();
        $includes[] = $member->getReferencedType();
        
        $this->commitVariable($member, $varType);
    }
    
    public function visitTextMember(Schema\TextMember $member)
    {
        foreach ($this->modifiers as $modifier)
        {
            $modifier->visitTextMember($member);
        }
        
        $varType = 'string';
        
        $this->commitVariable($member, $varType);
    }
    
    public function visitIntMember(Schema\IntMember $member)
    {
        foreach ($this->modifiers as $modifier)
        {
            $modifier->visitIntMember($member);
        }
        
        $varType = 'int';
        
        $this->commitVariable($member, $varType);
    }
    
    public function visitFloatMember(Schema\FloatMember $member)
    {
        foreach ($this->modifiers as $modifier)
        {
            $modifier->visitFloatMember($member);
        }
        
        $varType = 'float';
        
        $this->commitVariable($member, $varType);
    }
    
    public function visitDatetimeMember(Schema\DatetimeMember $member)
    {
        foreach ($this->modifiers as $modifier)
        {
            $modifier->visitDatetimeMember($member);
        }
        
        $varType = 'datetime';
        
        $this->commitVariable($member, $varType);
    }
    
    private function commitVariable(Schema\Member $member, $varType)
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
        
        $this->output .= '    private $' . $member->getName() . " = null;\n";
        $this->output .= "    \n";
        
        foreach ($this->modifiers as $modifier)
        {
            $this->output .= $modifier->varDefinitionAfter();
        }
        
        // accessors
        
        //getter
        $this->output .= '    ' . $access . ' function get' . \ucfirst($member->getName()) . "()\n";
        $this->output .= "    {\n";
        
        foreach ($this->modifiers as $modifier)
        {
            $this->output .= $modifier->getterBegin();
        }
        
        $this->output .= '        return $this->' . $member->getName() . ";\n";
        $this->output .= "    }\n";
        $this->output .= "    \n";

        if ($access == 'public')
        {
            $this->getters .= '            case \'' . $member->getName() . "':\n";
            $this->getters .= '                return $this->get' . \ucfirst($member->getName()) . "();\n";
            $this->getters .= "            \n";
        }
        
        //setter
        $this->output .= '    ' . $access . ' function set' . \ucfirst($member->getName()) . '($value)' . "\n";
        $this->output .= "    {\n";
        
        foreach ($this->modifiers as $modifier)
        {
            $this->output .= $modifier->setterBegin();
        }
        
        $this->output .= '        $this->' . $member->getName() . ' = $value;' . "\n";
        
        foreach ($this->modifiers as $modifier)
        {
            $this->output .= $modifier->setterEnd();
        }
        
        $this->output .= "    }\n";
        $this->output .= "    \n";
        
        if ($access == 'public')
        {
            $this->setters .= '            case \'' . $member->getName() . "':\n";
            $this->setters .= '                $this->set' . \ucfirst($member->getName()) . '($value);' . "\n";
            $this->setters .= "                break;\n";
            $this->setters .= "            \n";
        }
    }
}

?>