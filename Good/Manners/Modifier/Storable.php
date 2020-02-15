<?php

namespace Good\Manners\Modifier;

use Good\Rolemodel\Schema;
use Good\Manners\Modifier\Helpers\FromArrayParserWriter;
use Good\Manners\Modifier\Helpers\ToArrayFormatterWriter;

class Storable implements \Good\Service\Modifier, \Good\Rolemodel\TypeVisitor
{
    private $accept;
    private $setFromArray;
    private $toArray;
    private $debugInfo;

    private $resolver = null;
    private $resolverVisit = null;
    private $extraFiles;

    private $member;

    public function __construct()
    {
        $this->extraFiles = array();
    }

    public function implementingInterfaces()
    {
        return array('\\Good\\Manners\\GeneratedStorable');
    }

    public function baseClassConstructor()
    {
        return '';
    }

    public function baseClassBody()
    {
        $res  = "    // Storable\n";
        $res .= '    private $isNew = true;' . "\n";
        $res .= '    private $id = null;' . "\n";
        $res .= "    \n";
        $res .= '    public function isNew()'. "\n";
        $res .= "    {\n";
        $res .= '        return $this->isNew;' . "\n";
        $res .= "    }\n";
        $res .= "    \n";
        $res .= '    public function setNew($value)'. "\n";
        $res .= "    {\n";
        $res .= '        $this->isNew = $value;' . "\n";
        $res .= "    }\n";
        $res .= "    \n";
        $res .= '    public function getId()' . "\n";
        $res .= "    {\n";
        $res .= '        if ($this->id === null)' . "\n";
        $res .= "        {\n";
        $res .= '            throw new \Exception("Uninitilized id requested!");' . "\n";
        $res .= "        }\n";
        $res .= "        \n";
        $res .= '        return $this->id;' . "\n";
        $res .= "    }\n";
        $res .= "    \n";
        $res .= '    public function setId($value)' . "\n";
        $res .= "    {\n";
        $res .= '        if (!is_string($value) || strlen($value) == 0)' . "\n";
        $res .= "        {\n";
        $res .= '            throw new InvalidParameterException("Id must be a non-empty string");' . "\n";
        $res .= "        }\n";
        $res .= "\n";
        $res .= '        $this->id = $value;' . "\n";
        $res .= "    }\n";
        $res .= "    \n";
        $res .= '    public function hasValidId()' . "\n";
        $res .= "    {\n";
        $res .= '        return $this->id !== null;' . "\n";
        $res .= "    }\n";
        $res .= "    \n";

        return $res;
    }

    public function varDefinitionAfter(Schema\Member $member)
    {
        // ucfirst: upper case first letter (it's a php built-in)
        $res  = '    private $is' . \ucfirst($member->getName()) . 'Dirty =  false;' . "\n";
        $res .= "    \n";

        return $res;
    }

    public function getterBegin(Schema\Member $member)
    {
        $res  = '        $this->GMMStorable_checkValidationToken();' . "\n";
        $res .= "        \n";

        return $res;
    }

    public function setterBegin(Schema\Member $member)
    {
        $res  = '        $this->GMMStorable_checkValidationToken();' . "\n";
        $res .= "        \n";

        return $res;
    }

    public function setterEnd(Schema\Member $member)
    {
        $res  = "        \n";
        // ucfirst: upper case first letter (it's a php built-in)
        $res .= '        $this->is' . \ucfirst($member->getName()) . 'Dirty = true;' . "\n";
        $res .= '        $this->GMMStorable_makeDirty();' . "\n";

        return $res;
    }

    public function topOfGetterSwitch(Schema\TypeDefinition $typeDefinition)
    {
        $res  = '            case "id":' . "\n";
        $res .= '                return $this->getid();' . "\n";
        $res .= "            \n";

        return $res;
    }

    public function classBody(Schema\TypeDefinition $typeDefinition)
    {
        $res  = '    private $dirty = false;' . "\n";
        $res .= "    \n";
        $res .= '    private function GMMStorable_makeDirty()' . "\n";
        $res .= "    {\n";
        $res .= '        if (!$this->isDirty() && $this->storage != null)' . "\n";
        $res .= "        {\n";
        $res .= '            $this->dirty = true;' . "\n";
        $res .= '            $this->storage->dirtyStorable($this);' . "\n";
        $res .= "        }\n";
        $res .= "    }\n";
        $res .= "    \n";
        $res .= '    public function clean()' . "\n";
        $res .= "    {\n";
        $res .= '        $this->dirty = false;' . "\n";
        $res .= "        \n";

        foreach ($typeDefinition->getMembers() as $member)
        {
            $res .= '        $this->is' . ucfirst($member->getName()) . 'Dirty = false;' . "\n";
        }

        $res .= "    }\n";
        $res .= "    \n";
        $res .= '    public function isDirty()' . "\n";
        $res .= "    {\n";
        $res .= '        return $this->dirty;' . "\n";
        $res .= "    }\n";
        $res .= "    \n";
        $res .= '    private $validationToken = null;' . "\n";
        $res .= "    \n";
        $res .= '    private function GMMStorable_checkValidationToken()' . "\n";
        $res .= "    {\n";
        $res .= '        if ($this->validationToken != null && !$this->validationToken->value())' . "\n";
        $res .= "        {\n";
                        // TODO: turn this into decent error handling
        $res .= '            throw new \\Exception("Tried to acces an invalid Storable. It was probably made invalid by actions" .' . "\n";
        $res .= '                 " on its storage (like doing a modify, which invalidates all its Storables).");' . "\n";
        $res .= "        }\n";
        $res .= "    }\n";
        $res .= "    \n";
        $res .= '    public function setValidationToken(\\Good\\Manners\\ValidationToken $token)' . "\n";
        $res .= "    {\n";
        $res .= '        $this->validationToken = $token;' . "\n";
        $res .= "    }\n";
        $res .= "    \n";
        $res .= '    private $storage = null;' . "\n";
        $res .= "    \n";
        $res .= '    public function setStorage(\\Good\\Manners\\Storage $storage)' . "\n";
        $res .= "    {\n";
        $res .= '        $this->storage = $storage;' . "\n";
        $res .= "    }\n";
        $res .= "    \n";
        $res .= '    private $deleted = false;' . "\n";
        $res .= "    \n";
        $res .= '    public function isDeleted()'. "\n";
        $res .= "    {\n";
        $res .= '        return $this->deleted;' . "\n";
        $res .= "    }\n";
        $res .= "    \n";
        $res .= '    public function delete()'. "\n";
        $res .= "    {\n";
        $res .= '        $this->deleted = true;' . "\n";
        $res .= '        $this->GMMStorable_makeDirty();' . "\n";
        $res .= "    }\n";

        $res .= '    public static function resolver()' . "\n";
        $res .= "    {\n";
        $res .= '        return new ' . $typeDefinition->getName() . 'Resolver();' . "\n";
        $res .= "    }\n";
        $res .= "    \n";
        $res .= '    public static function id($storage, $id)' . "\n";
        $res .= "    {\n";
        $res .= '        return new \Good\Manners\Id(new ' . $typeDefinition->getName() . '(), $storage, $id);' . "\n";
        $res .= "    }\n";
        $res .= "    \n";
        $res .= '    public function getType()' . "\n";
        $res .= "    {\n";
        $res .= '        return "' . $typeDefinition->getName() . '";' . "\n";
        $res .= "    }\n";
        $res .= "    \n";

        $this->generateIncludableFragments($typeDefinition);

        $this->extraFiles[$typeDefinition->getName() . 'Resolver.php'] = $this->resolver;

        $res .= $this->accept;
        $res .= $this->setFromArray;
        $res .= $this->toArray;
        $res .= $this->debugInfo;

        return $res;
    }

    public function generateIncludableFragments(Schema\TypeDefinition $typeDefinition)
    {
        $this->accept  = '    public function acceptStorableVisitor(\\Good\\Manners\\StorableVisitor $visitor)' . "\n";
        $this->accept .= "    {\n";

        $this->setFromArray  = '    public function setFromArray(array $data)' . "\n";
        $this->setFromArray .= "    {\n";
        $this->setFromArray .= '        foreach ($data as $field => $value)' . "\n";
        $this->setFromArray .= "        {\n";
        $this->setFromArray .= '            switch ($field)' . "\n";
        $this->setFromArray .= "            {\n";

        $this->toArray  = '    public function toArray($datesToIso)' . "\n";
        $this->toArray .= "    {\n";
        $this->toArray .= "        return [\n";
        $this->toArray .= '            "id" => $this->id,' . "\n";

        $this->debugInfo  = '    public function __debugInfo()' . "\n";
        $this->debugInfo .= "    {\n";
        $this->debugInfo .= "        return [\n";
        $this->debugInfo .= '            "id" => $this->id,' . "\n";

        $this->resolver  = "<?php\n";
        $this->resolver .= "\n";
        $this->resolver .= 'class ' . $typeDefinition->getName() . 'Resolver extends \\Good\\Manners\\BaseResolver' . "\n";
        $this->resolver .= "{\n";
        $this->resolver .= '    public function getType()' . "\n";
        $this->resolver .= "    {\n";
        $this->resolver .= '        return "' . $typeDefinition->getName() . '";' . "\n";
        $this->resolver .= "    }\n";
        $this->resolver .= "    \n";

        $this->resolverVisit  = '    public function acceptResolverVisitor' .
                                                '(\\Good\\Manners\\ResolverVisitor $visitor)' . "\n";
        $this->resolverVisit .= "    {\n";

        foreach ($typeDefinition->getMembers() as $member)
        {
            $this->member = $member;

            $member->getType()->acceptTypeVisitor($this);
        }

        $this->accept .= "    }\n";
        $this->accept .= "    \n";

        $this->setFromArray .= '                default:' . "\n";
        $this->setFromArray .= '                    throw new Exception("Unrecognised field in setFromArray array.");' . "\n";
        $this->setFromArray .= '                    break;' . "\n";
        $this->setFromArray .= "            }\n";
        $this->setFromArray .= "        }\n";
        $this->setFromArray .= "    }\n";

        $this->toArray .= "        ];\n";
        $this->toArray .= "    }\n";
        $this->toArray .= "\n";

        $this->debugInfo .= "        ];\n";
        $this->debugInfo .= "    }\n";
        $this->debugInfo .= "\n";

        $this->resolverVisit .= "    }\n";
        $this->resolverVisit .= "    \n";

        $this->resolver .= $this->resolverVisit;

        $this->resolver .= "}\n";
        $this->resolver .= "\n";
        $this->resolver .= "?>";
    }

    public function visitDateTimeType(Schema\Type\DateTimeType $type)
    {
        $fromArrayParserWriter = new FromArrayParserWriter();
        $fromArrayParser = $fromArrayParserWriter->writeFromArrayParser($type);

        $this->accept .= '        $visitor->visitDatetimeProperty("' . $this->member->getName() . '", ' .
                                            '$this->is' . \ucfirst($this->member->getName()) . 'Dirty, ' .
                                            '$this->' . $this->member->getName() . ');' . "\n";

        $this->setFromArray .= '                case "' . $this->member->getName() . '":' . "\n";
        $this->setFromArray .= '                    $this->set' . \ucfirst($this->member->getName()) . '(' . $fromArrayParser . ');'. "\n";
        $this->setFromArray .= '                    break;' . "\n";

        $toArrayFormatterWriter = new ToArrayFormatterWriter();
        $toArrayFormatter = $toArrayFormatterWriter->writeToArrayFormatter('$this->' . $this->member->getName(), $type);

        $this->toArray .= '                "' . $this->member->getName() . '" => ' . $toArrayFormatter . ",\n";

        $this->visitNonReference();
    }

    public function visitIntType(Schema\Type\IntType $type)
    {
        $fromArrayParserWriter = new FromArrayParserWriter();
        $fromArrayParser = $fromArrayParserWriter->writeFromArrayParser($type);

        $this->accept .= '        $visitor->visitIntProperty("' . $this->member->getName() . '", ' .
                                            '$this->is' . \ucfirst($this->member->getName()) . 'Dirty, ' .
                                            '$this->' . $this->member->getName() . ');' . "\n";

        $this->setFromArray .= '                case "' . $this->member->getName() . '":' . "\n";
        $this->setFromArray .= '                    $this->set' . \ucfirst($this->member->getName()) . '(' . $fromArrayParser . ');'. "\n";
        $this->setFromArray .= '                    break;' . "\n";

        $toArrayFormatterWriter = new ToArrayFormatterWriter();
        $toArrayFormatter = $toArrayFormatterWriter->writeToArrayFormatter('$this->' . $this->member->getName(), $type);

        $this->toArray .= '                "' . $this->member->getName() . '" => ' . $toArrayFormatter . ",\n";

        $this->visitNonReference();
    }

    public function visitFloatType(Schema\Type\FloatType $type)
    {
        $fromArrayParserWriter = new FromArrayParserWriter();
        $fromArrayParser = $fromArrayParserWriter->writeFromArrayParser($type);

        $this->accept .= '        $visitor->visitFloatProperty("' . $this->member->getName() . '", ' .
                                            '$this->is' . \ucfirst($this->member->getName()) . 'Dirty, ' .
                                            '$this->' . $this->member->getName() . ');' . "\n";

        $this->setFromArray .= '                case "' . $this->member->getName() . '":' . "\n";
        $this->setFromArray .= '                    $this->set' . \ucfirst($this->member->getName()) . '(' . $fromArrayParser . ');'. "\n";
        $this->setFromArray .= '                    break;' . "\n";

        $toArrayFormatterWriter = new ToArrayFormatterWriter();
        $toArrayFormatter = $toArrayFormatterWriter->writeToArrayFormatter('$this->' . $this->member->getName(), $type);

        $this->toArray .= '                "' . $this->member->getName() . '" => ' . $toArrayFormatter . ",\n";

        $this->visitNonReference();
    }

    public function visitReferenceType(Schema\Type\ReferenceType $type)
    {
        $this->accept .= '        $visitor->visitReferenceProperty("' . $this->member->getName() . '", ' .
                                            '"' . $type->getReferencedType() . '", ' .
                                            '$this->is' . \ucfirst($this->member->getName()) . 'Dirty, ' .
                                            '$this->' . $this->member->getName() . ');' . "\n";

        $this->setFromArray .= '                case "' . $this->member->getName() . '":' . "\n";
        $this->setFromArray .= '                    $this->set' . \ucfirst($this->member->getName()) . '($value);'. "\n";
        $this->setFromArray .= '                    break;' . "\n";

        $toArrayFormatterWriter = new ToArrayFormatterWriter();
        $toArrayFormatter = $toArrayFormatterWriter->writeToArrayFormatter('$this->' . $this->member->getName(), $type);

        $this->toArray .= '                "' . $this->member->getName() . '" => ' . $toArrayFormatter . ",\n";

        $this->resolver .= '    private $resolved' . \ucfirst($this->member->getName()) . ' = null;' . "\n";
        $this->resolver .= "    \n";
        $this->resolver .= '    public function resolve' . \ucfirst($this->member->getName()) . '()' . "\n";
        $this->resolver .= "    {\n";
        $this->resolver .= '        $this->resolved' . \ucfirst($this->member->getName()) . ' = ' .
                                                        'new ' . $type->getReferencedType() .
                                                                'Resolver($this->root);' . "\n";
        $this->resolver .= "        \n";
        $this->resolver .= '        return $this->resolved' . \ucfirst($this->member->getName()) . ';' . "\n";
        $this->resolver .= "    }\n";
        $this->resolver .= "    \n";
        $this->resolver .= '    public function get' . \ucfirst($this->member->getName()) . '()' . "\n";
        $this->resolver .= "    {\n";
        $this->resolver .= '        return $this->resolved' . \ucfirst($this->member->getName()) . ';' . "\n";
        $this->resolver .= "    }\n";
        $this->resolver .= "    \n";

        $this->resolverVisit .= '        if ($this->resolved' . \ucfirst($this->member->getName()) . ' != null)' . "\n";
        $this->resolverVisit .= "        {\n";
        $this->resolverVisit .= '            $visitor->resolverVisitResolvedReferenceProperty("' .
                                            $this->member->getName() . '", "' . $type->getReferencedType() .
                                            '", ' . '$this->resolved' . \ucfirst($this->member->getName()) .
                                            ');' . "\n";
        $this->resolverVisit .= "        }\n";
        $this->resolverVisit .= '        else' . "\n";
        $this->resolverVisit .= "        {\n";
        $this->resolverVisit .= '            $visitor->resolverVisitUnresolvedReferenceProperty(' .
                                            '"' . $this->member->getName() . '");' . "\n";
        $this->resolverVisit .= "        }\n";

        $this->debugInfo .= '            "' . $this->member->getName() . '" => $this->' . $this->member->getName() . ',' . "\n";
        $this->debugInfo .= '            "is' . \ucfirst($this->member->getName()) . 'Dirty" => $this->is' . \ucfirst($this->member->getName()) . 'Dirty,' . "\n";
    }

    public function visitTextType(Schema\Type\TextType $type)
    {
        $fromArrayParserWriter = new FromArrayParserWriter();
        $fromArrayParser = $fromArrayParserWriter->writeFromArrayParser($type);

        $this->accept .= '        $visitor->visitTextProperty("' . $this->member->getName() . '", ' .
                                            '$this->is' . \ucfirst($this->member->getName()) . 'Dirty, ' .
                                            '$this->' . $this->member->getName() . ');' . "\n";

        $this->setFromArray .= '                case "' . $this->member->getName() . '":' . "\n";
        $this->setFromArray .= '                    $this->set' . \ucfirst($this->member->getName()) . '(' . $fromArrayParser . ');'. "\n";
        $this->setFromArray .= '                    break;' . "\n";

        $toArrayFormatterWriter = new ToArrayFormatterWriter();
        $toArrayFormatter = $toArrayFormatterWriter->writeToArrayFormatter('$this->' . $this->member->getName(), $type);

        $this->toArray .= '                "' . $this->member->getName() . '" => ' . $toArrayFormatter . ",\n";

        $this->visitNonReference();
    }

    public function visitCollectionType(Schema\Type\CollectionType $type)
    {
        $this->accept .= '        $visitor->visitCollectionProperty("' . $this->member->getName() . '", ' .
                                            '$this->' . $this->member->getName() . ');' . "\n";

        $fromArrayParserWriter = new FromArrayParserWriter();
        $fromArrayParser = $fromArrayParserWriter->writeFromArrayParser($type);

        $this->setFromArray .= '                case "' . $this->member->getName() . '":' . "\n";
        $this->setFromArray .= '                    $this->' . $this->member->getName() . '->clear();'. "\n";
        $this->setFromArray .= '                    foreach(' . $fromArrayParser . ' as $singleValue)'. "\n";
        $this->setFromArray .= "                    {\n";
        $this->setFromArray .= '                        $this->' . $this->member->getName() . '->add($singleValue);'. "\n";
        $this->setFromArray .= "                    }\n";
        $this->setFromArray .= '                    break;' . "\n";

        $toArrayFormatterWriter = new ToArrayFormatterWriter();
        $toArrayFormatter = $toArrayFormatterWriter->writeToArrayFormatter('$this->' . $this->member->getName(), $type);

        $this->toArray .= '                "' . $this->member->getName() . '" => ' . $toArrayFormatter . ",\n";

        $this->visitNonReference();
    }

    private function visitNonReference()
    {
        $this->resolver .= '    private $orderNumber' . \ucfirst($this->member->getName()) . ' = -1;' . "\n";
        $this->resolver .= '    private $orderDirection' . \ucfirst($this->member->getName()) . ' = -1;' . "\n";
        $this->resolver .= "    \n";
        $this->resolver .= '    public function orderBy' . \ucfirst($this->member->getName()) . 'Asc()' . "\n";
        $this->resolver .= "    {\n";
        $this->resolver .= '        $this->orderNumber' . \ucfirst($this->member->getName()) .
                                                        ' = $this->drawOrderTicket();' . "\n";
        $this->resolver .= '        $this->orderDirection' . \ucfirst($this->member->getName()) .
                                                        ' = self::ORDER_DIRECTION_ASC;' . "\n";
        $this->resolver .= '        return $this;' . "\n";
        $this->resolver .= "    }\n";
        $this->resolver .= "    \n";
        $this->resolver .= '    public function orderBy' . \ucfirst($this->member->getName()) . 'Desc()' . "\n";
        $this->resolver .= "    {\n";
        $this->resolver .= '        $this->orderNumber' . \ucfirst($this->member->getName()) .
                                                        ' = $this->drawOrderTicket();' . "\n";
        $this->resolver .= '        $this->orderDirection' . \ucfirst($this->member->getName()) .
                                                        ' = self::ORDER_DIRECTION_DESC;' . "\n";
        $this->resolver .= '        return $this;' . "\n";
        $this->resolver .= "    }\n";
        $this->resolver .= "    \n";

        $this->resolverVisit .= '        $visitor->resolverVisitNonReferenceProperty("' .
                                                                            $this->member->getName() . '");' . "\n";
        $this->resolverVisit .= '        if ($this->orderNumber' . \ucfirst($this->member->getName()) . ' != -1)' . "\n";
        $this->resolverVisit .= "        {\n";
        $this->resolverVisit .= '            if ($this->orderDirection' . \ucfirst($this->member->getName()) .
                                                                '== self::ORDER_DIRECTION_ASC)' . "\n";
        $this->resolverVisit .= "            {\n";
        $this->resolverVisit .= '                $visitor->resolverVisitOrderAsc($this->orderNumber'
                                                                        . \ucfirst($this->member->getName()) . ', "'
                                                                        . $this->member->getName() . '");' . "\n";
        $this->resolverVisit .= "            }\n";
        $this->resolverVisit .= '            else' . "\n";
        $this->resolverVisit .= "            {\n";
        $this->resolverVisit .= '                $visitor->resolverVisitOrderDesc($this->orderNumber'
                                                    . \ucfirst($this->member->getName()) . ', "'
                                                    . $this->member->getName() . '");' . "\n";
        $this->resolverVisit .= "            }\n";
        $this->resolverVisit .= "        }\n";

        $this->debugInfo .= '            "' . $this->member->getName() . '" => $this->' . $this->member->getName() . ',' . "\n";
        $this->debugInfo .= '            "is' . \ucfirst($this->member->getName()) . 'Dirty" => $this->is' . \ucfirst($this->member->getName()) . 'Dirty,' . "\n";
    }

    public function extraFiles()
    {
        return $this->extraFiles;

        $this->extraFiles = array();
    }
}

?>
