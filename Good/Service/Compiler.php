<?php

namespace Good\Service;

use Good\Rolemodel\Schema;

class Compiler implements \Good\Rolemodel\TypeVisitor
{
    // TODO: prevent namespace collisions between things between
    //       modifiers and generated variables / accessors / type name

    private $outputDir;
    private $modifiers;

    private $inputFile = null;
    private $output = null;
    private $getters = null;
    private $setters = null;
    private $constructor = null;
    private $staticInitalize = null;

    private $member;

    public function __construct($modifiers, $outputDir)
    {
        $this->outputDir = $outputDir;
        $this->modifiers = $modifiers;
    }

    public function compile(Schema $schema)
    {

        $this->generateBaseClass($schema);

        foreach ($schema->getTypeDefitions() as $typeDefinition)
        {
            $this->inputFile = $typeDefinition->getSourceFileName();

            $this->startType($typeDefinition);

            foreach ($typeDefinition->getMembers() as $member)
            {
                $this->member = $member;

                $member->getType()->acceptTypeVisitor($this);
            }

            $this->wrapUpType($typeDefinition);

            $outputFile = $this->outputDir . $typeDefinition->getName() . '.datatype.php';
            \file_put_contents($outputFile, $this->output);
        }

        $this->output = null;

        foreach ($this->modifiers as $modifier)
        {
            foreach($modifier->extraFiles() as $filename => $contents)
            {
                \file_put_contents($this->outputDir . $filename, $contents);
            }
        }
    }

    private function generateBaseClass(Schema $schema)
    {
        // TODO: prevent namespace and filename collisions here
        // Build the base class
        $output  = "<?php\n";
        $output .= "\n";
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

    private function startType(Schema\TypeDefinition $typeDefinition)
    {
        $this->output = '';

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

        $this->constructor  = "    public function __construct()\n";
        $this->constructor .= "    {\n";

        $this->staticInitalize  = "    public static function staticInitalize()\n";
        $this->staticInitalize .= "    {\n";
    }

    public function visitDateTimeType(Schema\Type\DateTimeType $type)
    {
        $this->commitVariable($this->member, $type, true);
    }

    public function visitIntType(Schema\Type\IntType $type)
    {
        $this->commitVariable($this->member, $type, true);
    }

    public function visitFloatType(Schema\Type\FloatType $type)
    {
        $this->commitVariable($this->member, $type, true);
    }

    public function visitReferenceType(Schema\Type\ReferenceType $type)
    {
        $this->commitVariable($this->member, $type, true);
    }

    public function visitTextType(Schema\Type\TextType $type)
    {
        $this->commitVariable($this->member, $type, true);
    }

    public function visitCollectionType(Schema\Type\CollectionType $type)
    {
        $this->constructor .= '        $this->' . $this->member->getName()
            . ' = new \Good\Service\Collection('
            . 'self::$' . $this->member->getName() . 'Type->getCollectedType());' . "\n";

        $this->commitVariable($this->member, $type, false);
    }

    private function commitVariable(Schema\Member $member, Schema\Type $type, $settable)
    {
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

        $this->output .= '    public static $' . $this->member->getName() . "Type;\n";
        $this->output .= "\n";

        $typeGeneratorWriter = new TypeGeneratorWriter();
        $typeGenerator = $typeGeneratorWriter->getTypeGenerator($type, $this->member->getName());

        $this->staticInitalize .= '        self::$' . $this->member->getName() . 'Type = ' . $typeGenerator . ";\n";

        $this->output .= '    private $' . $member->getName() . " = null;\n";
        $this->output .= "    \n";

        foreach ($this->modifiers as $modifier)
        {
            $this->output .= $modifier->varDefinitionAfter($member);
        }

        // accessors

        // getter
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
        if ($settable)
        {
            $this->output .= '    ' . $access . ' function set' . \ucfirst($member->getName()) . '($value)' . "\n";

            $this->output .= "    {\n";

            $this->output .= '        self::$' . $member->getName() . 'Type->checkValue($value);' . "\n";
            $this->output .= "\n";

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
        }

        if ($access == 'public')
        {
            $this->setters .= '            case \'' . $member->getName() . "':\n";

            if ($settable)
            {
                $this->setters .= '                $this->set' . \ucfirst($member->getName()) . '($value);' . "\n";
            }
            else
            {
                $this->setters .= '                throw new \Exception("Property \"' . $member->getName() . '\" cannot be set");' . "\n";
            }

            $this->setters .= "                break;\n";
            $this->setters .= "            \n";
        }
    }

    private function wrapUpType(Schema\TypeDefinition $typeDefinition)
    {
        $this->getters .= '            default:' . "\n";
        $this->getters .= '                throw new \Exception("Unknown or non-public property");' . "\n";
        $this->getters .= "        }\n";
        $this->getters .= "    }\n";
        $this->getters .= "\n";

        $this->setters .= '            default:' . "\n";
        $this->setters .= '                throw new \Exception("Unknown or non-public property");' . "\n";
        $this->setters .= "        }\n";
        $this->setters .= "    }\n";
        $this->setters .= "\n";

        $this->output .= $this->getters;
        $this->output .= $this->setters;

        foreach ($this->modifiers as $modifier)
        {
            $this->output .= $modifier->classBody($typeDefinition);
        }

        $this->output .= "}\n";
        $this->output .= "\n";
        $this->output .= $typeDefinition->getName() . "::staticInitalize();\n";
        $this->output .= "\n";

        foreach ($this->modifiers as $modifier)
        {
            $this->constructor .= $modifier->constructor();
        }

        $this->constructor .= "    }\n";
        $this->constructor .= "\n";

        $this->staticInitalize .= "    }\n";
        $this->staticInitalize .= "\n";

        // neatly start the file
        $top  = "<?php\n";
        $top .= "\n";

        $top .= "include_once 'GeneratedBaseClass.php';\n";
        $top .= "\n";

        // Start class
        // This was moved here so the constructor could be on top
        $top .= 'class ' . $typeDefinition->getName() . " extends GeneratedBaseClass\n";
        $top .= "{\n";

        $top .= $this->constructor;
        $top .= $this->staticInitalize;

        $this->output = $top . $this->output;

        // close the file off
        $this->output .= "\n";
        $this->output .= "?>";
    }
}

?>
