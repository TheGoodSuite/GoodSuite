<?php

namespace Good\Service;

use Good\Rolemodel\Schema;

class Compiler implements \Good\Rolemodel\TypeVisitor
{
    // TODO: prevent namespace collisions between things between
    //       modifiers and generated variables / accessors

    // Compiler level data
    private $outputDir;
    private $modifiers;

    // file level data
    private $inputFile = null;
    private $outputFile = null;
    private $outputFiles = array();
    private $output = null;
    private $includes = null;
    private $className = null;
    private $getters = null;
    private $setters = null;

    // Refactoring 2020
    private $member;

    public function __construct($modifiers, $outputDir)
    {
        $this->outputDir = $outputDir;
        $this->modifiers = $modifiers;
    }

    public function compiledFiles()
    {
        return $this->outputFiles;
    }

    public function compile(Schema $schema)
    {
        $this->visitSchema($schema);

        foreach ($schema->getTypeDefitions() as $typeDefinition)
        {
            $this->visitTypeDefinition($typeDefinition);

            foreach ($typeDefinition->getMembers() as $member)
            {
                $this->member = $member;

                $member->getType()->acceptTypeVisitor($this);
            }

            $this->saveOutput($typeDefinition);
        }

        $this->visitSchemaEnd();
    }

    public function visitDateTimeType(Schema\Type\DateTimeType $type)
    {
        $this->visitDatetimeMember($this->member, $type);
    }

    public function visitIntType(Schema\Type\IntType $type)
    {
        $this->visitIntMember($this->member, $type);
    }

    public function visitFloatType(Schema\Type\FloatType $type)
    {
        $this->visitFloatMember($this->member, $type);
    }

    public function visitReferenceType(Schema\Type\ReferenceType $type)
    {
        $this->visitReferenceMember($this->member, $type);
    }

    public function visitTextType(Schema\Type\TextType $type)
    {
        $this->visitTextMember($this->member, $type);
    }

    public function visitSchema(Schema $schema)
    {
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
        $this->output = null;

        foreach ($this->modifiers as $modifier)
        {
            foreach($modifier->extraFiles() as $filename => $contents)
            {
                \file_put_contents($this->outputDir . $filename, $contents);
            }
        }
    }

    public function visitTypeDefinition(Schema\TypeDefinition $typeDefinition)
    {
        $this->className = $typeDefinition->getName();

        $this->includes = array();

        $this->output = 'class ' . $typeDefinition->getName() . " extends GeneratedBaseClass\n";

        $this->output .= "{\n";
        $this->inputFile = $typeDefinition->getSourceFileName();
        // TODO: make following line independant of execution path at any time
        //       and escape some stuff
        // Note: This was previously based on the input file namespace
        //       But I changed it to dataType name instead
        $this->outputFile = $this->outputDir . $typeDefinition->getName() . '.datatype.php';
        $this->outputFiles[] = $this->outputFile;

        $this->getters  = '    public function __get($property)' . "\n";
        $this->getters .= "    {\n";
        $this->getters .= '        switch ($property)' . "\n";
        $this->getters .= "        {\n";

        foreach ($this->modifiers as $modifier)
        {
            $this->getters .= $modifier->topOfGetterSwitch($typeDefinition);
        }

        $this->setters  = '    public function __set($property, $value)' . "\n";
        $this->setters .= "    {\n";
        $this->setters .= '        switch ($property)' . "\n";
        $this->setters .= "        {\n";
    }

    private function saveOutput(Schema\TypeDefinition $typeDefinition)
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
            $this->output .= $modifier->classBody($typeDefinition);
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

    public function visitReferenceMember(Schema\Member $member, Schema\Type\ReferenceType $type)
    {
        $varType = $type->getReferencedType();
        $typeCheck = null;

        $includes[] = $type->getReferencedType();

        $this->commitVariable($member, $varType, $typeCheck);
    }

    public function visitTextMember(Schema\Member $member, Schema\Type\TextType $type)
    {
        $typeModifiers = $type->getTypeModifiers();

        $varType = 'string';
        $typeCheck = '\\Good\\Service\\TypeChecker::checkString($value, ' . $typeModifiers['minLength'];

        if (array_key_exists('maxLength', $typeModifiers))
        {
            $typeCheck .= ', ' . $typeModifiers['maxLength'];
        }

        $typeCheck .= ')';

        $this->commitVariable($member, $varType, $typeCheck);
    }

    public function visitIntMember(Schema\Member $member, Schema\Type\IntType $type)
    {
        $typeModifiers = $type->getTypeModifiers();

        $varType = 'int';
        $typeCheck = '\\Good\\Service\\TypeChecker::checkInt($value, ' . $typeModifiers['minValue'];
        $typeCheck .= ', ' . $typeModifiers['maxValue'] . ')';

        $this->commitVariable($member, $varType, $typeCheck);
    }

    public function visitFloatMember(Schema\Member $member, Schema\Type\FloatType $type)
    {
        $varType = 'float';
        $typeCheck = '\\Good\\Service\\TypeChecker::checkFloat($value)';

        $this->commitVariable($member, $varType, $typeCheck);
    }

    public function visitDatetimeMember(Schema\Member $member, Schema\Type\DatetimeType $type)
    {
        $varType = 'datetime';
        $typeCheck = '\\Good\\Service\\TypeChecker::checkDateTime($value)';

        $this->commitVariable($member, $varType, $typeCheck);
    }

    private function commitVariable(Schema\Member $member, $varType, $typeCheck)
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
            $this->output .= $modifier->varDefinitionBefore($member);
        }

        $this->output .= '    private $' . $member->getName() . " = null;\n";
        $this->output .= "    \n";

        foreach ($this->modifiers as $modifier)
        {
            $this->output .= $modifier->varDefinitionAfter($member);
        }

        // accessors

        //getter
        $this->output .= '    ' . $access . ' function get' . \ucfirst($member->getName()) . "()\n";
        $this->output .= "    {\n";

        foreach ($this->modifiers as $modifier)
        {
            $this->output .= $modifier->getterBegin($member);
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

        if ($typeCheck != null)
        {
            $this->output .= '        ' . $typeCheck . ";\n";
            $this->output .= "        \n";
        }

        foreach ($this->modifiers as $modifier)
        {
            $this->output .= $modifier->setterBegin($member);
        }

        $this->output .= '        $this->' . $member->getName() . ' = $value;' . "\n";

        foreach ($this->modifiers as $modifier)
        {
            $this->output .= $modifier->setterEnd($member);
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
