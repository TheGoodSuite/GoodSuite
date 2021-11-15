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
    private $constructor;
    private $classBodyContent;
    private $clean;
    private $markUnresolved;
    private $isDirty;

    private $condition;
    private $conditionSetterSwitch;
    private $conditionGetterSwitch;
    private $conditionProcess;

    private $typeDefinition;
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
        $res .= '    protected $id = null;' . "\n";
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
        $res .= '            throw new \\Good\\Service\\InvalidParameterException("Id must be a non-empty string");' . "\n";
        $res .= "        }\n";
        $res .= "\n";
        $res .= '        $this->id = $value;' . "\n";
        $res .= "    }\n";
        $res .= "    \n";
        $res .= '    public function hasValidId()' . "\n";
        $res .= "    {\n";
        $res .= '        return $this->id !== null;' . "\n";
        $res .= "    }\n";
        $res .= "\n";

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
        $res  = '        if ($this->isUnresolved)' . "\n";
        $res .= "        {\n";
        $res  = '            throw new \Exception("Cannot set or get properties of an unresolved storable");' . "\n";
        $res .= "        }\n";
        $res .= "        \n";
        $res  = '        $this->GMMStorable_checkValidationToken();' . "\n";
        $res .= "        \n";

        return $res;
    }

    public function setterBegin(Schema\Member $member)
    {
        $res  = '        if ($this->isUnresolved)' . "\n";
        $res .= "        {\n";
        $res  = '            throw new \Exception("Cannot set or get properties of an unresolved storable");' . "\n";
        $res .= "        }\n";
        $res .= "        \n";
        $res  = '        $this->GMMStorable_checkValidationToken();' . "\n";
        $res .= "        \n";

        return $res;
    }

    public function setterEnd(Schema\Member $member)
    {
        $res  = "        \n";
        // ucfirst: upper case first letter (it's a php built-in)
        $res .= '        $this->is' . \ucfirst($member->getName()) . 'Dirty = true;' . "\n";

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
        $res  = '    private $validationToken = null;' . "\n";
        $res .= '    protected $isUnresolved = false;' . "\n";
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
        $res .= '        if ($this->isUnresolved)' . "\n";
        $res .= "        {\n";
        $res .= '            $this->storage->insert($this);' . "\n";
        $res .= "        }\n";
        $res .= "\n";
        $res .= '        $this->deleted = true;' . "\n";
        $res .= "    }\n";

        $res .= '    public static function resolver()' . "\n";
        $res .= "    {\n";
        $res .= '        return new ' . $typeDefinition->getName() . 'Resolver();' . "\n";
        $res .= "    }\n";
        $res .= "    \n";
        $res .= '    public static function condition()' . "\n";
        $res .= "    {\n";
        $res .= '        return new ' . $typeDefinition->getName() . 'Condition();' . "\n";
        $res .= "    }\n";
        $res .= "    \n";
        $res .= '    public static function id($storage, $id)' . "\n";
        $res .= "    {\n";
        $res .= '        $result = new ' . $typeDefinition->getName() . '();' . "\n";
        $res .= "\n";
        $res .= '        $result->storage = $storage;' . "\n";
        $res .= '        $result->id = $id;' . "\n";
        $res .= '        $result->isUnresolved = true;' . "\n";
        $res .= '        $result->setNew(false);' . "\n";
        $res .= '        $result->clean();' . "\n";
        $res .= "\n";
        $res .= '        return $result;' . "\n";
        $res .= "    }\n";
        $res .= "    \n";
        $res .= '    public function getType()' . "\n";
        $res .= "    {\n";
        $res .= '        return "' . $typeDefinition->getName() . '";' . "\n";
        $res .= "    }\n";
        $res .= "    \n";
        $res .= '    public function fetch(' . $typeDefinition->getName() . 'Resolver $resolver = null)' . "\n";
        $res .= "    {\n";
        $res .= '        if (!$this->isUnresolved)' . "\n";
        $res .= "        {\n";
        $res .= '            throw new \Exception("Can only fetch unresolved Storables");' . "\n";
        $res .= "        }\n";
        $res .= "\n";
        $res .= '        $condition = $this->getType()::condition();' . "\n";
        $res .= '        $condition->id = $this->getId();' . "\n";
        $res .= "\n";
        $res .= '        $results = $this->storage->fetchAll($condition, $resolver);' . "\n";
        $res .= "\n";
        $res .= '        $first = $results->getNext();' . "\n";
        $res .= "\n";
        $res .= '        if ($first === null)' . "\n";
        $res .= "        {\n";
        $res .= '            throw new \Exception("Id not found in storage");' . "\n";
        $res .= "        }\n";
        $res .= "\n";
        $res .= '        return $first;' . "\n";
        $res .= "    }\n";
        $res .= "    \n";

        $this->generateIncludableFragments($typeDefinition);

        $this->extraFiles[$typeDefinition->getName() . 'Resolver.php'] = $this->resolver;
        $this->extraFiles[$typeDefinition->getName() . 'Condition.php'] = $this->condition;

        $res .= $this->isDirty;
        $res .= $this->clean;
        $res .= $this->markUnresolved;
        $res .= $this->classBodyContent;
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

        $this->condition  = "<?php\n";
        $this->condition .= "\n";
        $this->condition .= 'use \Good\Manners\Condition;' . "\n";
        $this->condition .= 'use \Good\Manners\CollectionCondition;' . "\n";
        $this->condition .= 'use \Good\Manners\Condition\ComplexCondition;' . "\n";
        $this->condition .= 'use \Good\Manners\Condition\EqualTo;' . "\n";
        $this->condition .= 'use \Good\Manners\Processors\ConditionProcessor;' . "\n";
        $this->condition .= 'use \Good\Manners\Processors\ComplexConditionProcessor;' . "\n";
        $this->condition .= 'use \Good\Service\Type;' . "\n";
        $this->condition .= "\n";
        $this->condition .= 'class ' . $typeDefinition->getName() . 'Condition implements ComplexCondition' . "\n";
        $this->condition .= "{\n";
        $this->condition .= '    public function getTargetType()' . "\n";
        $this->condition .= "    {\n";
        $this->condition .= '        return"' . $typeDefinition->getName() . '";' . "\n";
        $this->condition .= "    }\n";
        $this->condition .= "\n";
        $this->condition .= '    private $id = null;' . "\n";
        $this->condition .= "\n";
        $this->condition .= '    public function setId(Condition $condition)' . "\n";
        $this->condition .= "    {\n";
        $this->condition .= '        $this->id = $condition;' . "\n";
        $this->condition .= "    }\n";
        $this->condition .= "\n";
        $this->condition .= '    public function processCondition(ConditionProcessor $processor)' . "\n";
        $this->condition .= "    {\n";
        $this->condition .= '        $processor->processComplexCondition($this);' . "\n";
        $this->condition .= "    }\n";
        $this->condition .= "\n";
        $this->condition .= '    public function appliesToType(Type $type)' . "\n";
        $this->condition .= "    {\n";
        $this->condition .= '        return $type->checkValue(new ' . $typeDefinition->getName() . '());' . "\n";
        $this->condition .= "    }\n";
        $this->condition .= "\n";

        $this->conditionSetterSwitch  = '        switch($property)' . "\n";
        $this->conditionSetterSwitch .= "        {\n";
        $this->conditionSetterSwitch .= '            case "id":' . "\n";
        $this->conditionSetterSwitch .= '                $this->setId($value instanceof Condition ? $value : new EqualTo($value));' . "\n";
        $this->conditionSetterSwitch .= '                break;' . "\n";
        $this->conditionSetterSwitch .= "\n";

        $this->conditionGetterSwitch  = '        switch($property)' . "\n";
        $this->conditionGetterSwitch .= "        {\n";

        $this->conditionComplexProcess  = '    public function processComplexCondition(ComplexConditionProcessor $processor)' . "\n";
        $this->conditionComplexProcess .= "    {\n";
        $this->conditionComplexProcess .= '        if ($this->id !== null)' . "\n";
        $this->conditionComplexProcess .= "        {\n";
        $this->conditionComplexProcess .= '            $processor->processId($this->id);' . "\n";
        $this->conditionComplexProcess .= "        }\n";

        $this->resolverVisit  = '    public function acceptResolverVisitor' .
                                                '(\\Good\\Manners\\ResolverVisitor $visitor)' . "\n";
        $this->resolverVisit .= "    {\n";

        $this->constructor = '';
        $this->classBodyContent = '';

        $this->clean  = '    public function clean()' . "\n";
        $this->clean .= "    {\n";

        $this->isDirty  = '    public function isDirty()' . "\n";
        $this->isDirty .= "    {\n";
        $this->isDirty .= '        return $this->deleted';

        $this->markUnresolved  = '    public function markCollectionsUnresolved()' . "\n";
        $this->markUnresolved .= "    {\n";
        $this->markUnresolved .= "        \n";

        foreach ($typeDefinition->getMembers() as $member)
        {
            $this->member = $member;
            $this->clean .= '        $this->is' . ucfirst($member->getName()) . 'Dirty = false;' . "\n";

            $this->typeDefinition = $typeDefinition;

            $member->getType()->acceptTypeVisitor($this);
        }

        $this->markUnresolved .= "    }\n";
        $this->markUnresolved .= "    \n";

        $this->isDirty .= ";\n";
        $this->isDirty .= "    }\n";
        $this->isDirty .= "    \n";

        $this->clean .= "    }\n";
        $this->clean .= "    \n";

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

        $this->conditionSetterSwitch .= '            default:' . "\n";
        $this->conditionSetterSwitch .= '                throw new \Exception("Unknown property.");' . "\n";
        $this->conditionSetterSwitch .= "        }\n";

        $this->conditionGetterSwitch .= '            default:' . "\n";
        $this->conditionGetterSwitch .= '                throw new \Exception("Unknown property.");' . "\n";
        $this->conditionGetterSwitch .= "        }\n";

        $this->conditionComplexProcess .= "    }\n";
        $this->conditionComplexProcess .= "\n";

        $this->condition .= $this->conditionComplexProcess;
        $this->condition .= '    public function __set($property, $value)' . "\n";
        $this->condition .= "    {\n";
        $this->condition .= $this->conditionSetterSwitch;
        $this->condition .= "    }\n";
        $this->condition .= "\n";
        $this->condition .= '    public function __get($property)' . "\n";
        $this->condition .= "    {\n";
        $this->condition .= $this->conditionGetterSwitch;
        $this->condition .= "    }\n";
        $this->condition .= "}\n";
        $this->condition .= "\n";
        $this->condition .= "?>";
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

        $this->visitNonReference(true, true);
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

        $this->visitNonReference(true, true);
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

        $this->visitNonReference(true, true);
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

        $this->isDirty .= "\n";
        $this->isDirty .= '            || $this->is' . \ucfirst($this->member->getName()) . 'Dirty';

        $this->condition .= '    private $' . $this->member->getName() . ' = null;' . "\n";
        $this->condition .= '    private $' . $this->member->getName() . 'IsComplex;' . "\n";
        $this->condition .= "\n";
        $this->condition .= '    public function get' . \ucfirst($this->member->getName()) . '()' . "\n";
        $this->condition .= "    {\n";
        $this->condition .= '        if ($this->' . $this->member->getName() . ' == null)' . "\n";
        $this->condition .= "        {\n";
        $this->condition .= '             $this->' . $this->member->getName() . ' = ' . $type->getReferencedType() . '::condition();' . "\n";
        $this->condition .= '             $this->' . $this->member->getName() . 'IsComplex = true;' . "\n";
        $this->condition .= "        }\n";
        $this->condition .= "\n";
        $this->condition .= '        if (!$this->' . $this->member->getName() . 'IsComplex)' . "\n";
        $this->condition .= "        {\n";
        $this->condition .= '             throw new \Exception("Can only get a reference member if it is a complex condition");' . "\n";
        $this->condition .= "        }\n";
        $this->condition .= "\n";
        $this->condition .= '        return $this->' . $this->member->getName() . ';' . "\n";
        $this->condition .= "    }\n";
        $this->condition .= "\n";
        $this->condition .= '    public function set' . \ucfirst($this->member->getName()) . '(Condition $condition)' . "\n";
        $this->condition .= "    {\n";
        $this->condition .= '        $problem = $condition->appliesToType(' . $this->typeDefinition->getName() . '::$' . $this->member->getName() . 'Type);' . "\n";
        $this->condition .= "\n";
        $this->condition .= '        if ($problem !== null)' . "\n";
        $this->condition .= "        {\n";
        $this->condition .= '             throw new \Exception("Condition cannot be applied to this property: " . $problem);' . "\n";
        $this->condition .= "        }\n";
        $this->condition .= "\n";
        $this->condition .= '        return $this->' . $this->member->getName() . ' = $condition;' . "\n";
        $this->condition .= "    }\n";
        $this->condition .= "\n";

        $this->conditionGetterSwitch .= '            case "' . $this->member->getName() . '":' . "\n";
        $this->conditionGetterSwitch .= '                return $this->get' . \ucfirst($this->member->getName()) . '();' . "\n";
        $this->conditionGetterSwitch .= "\n";

        $this->conditionSetterSwitch .= '            case "' . $this->member->getName() . '":' . "\n";
        $this->conditionSetterSwitch .= '                $this->set' . \ucfirst($this->member->getName());
        $this->conditionSetterSwitch .= '($value instanceof Condition ? $value : new EqualTo($value));' . "\n";
        $this->conditionSetterSwitch .= '                break;' . "\n";
        $this->conditionSetterSwitch .= "\n";

        $this->conditionComplexProcess .= '        if ($this->' . $this->member->getName() . ' !== null)' . "\n";
        $this->conditionComplexProcess .= "        {\n";
        $this->conditionComplexProcess .= '            $processor->processMember(' . $this->typeDefinition->getName();
        $this->conditionComplexProcess .= '::$' . $this->member->getName() . 'Type, "' . $this->member->getName();
        $this->conditionComplexProcess .= '", $this->' . $this->member->getName() . ');' . "\n";
        $this->conditionComplexProcess .= "        }\n";

        $this->writeResolvableMemberToResolver($this->member->getName(), $type->getReferencedType());
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

        $this->visitNonReference(true, true);
    }

    public function visitCollectionType(Schema\Type\CollectionType $type)
    {
        $this->accept .= '        $visitor->visitCollectionProperty("' . $this->member->getName() . '", ' .
                                            '$this->' . $this->member->getName() . ', ' .
                                            '$this->' . $this->member->getName() . 'Modifier);' . "\n";

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

        $this->resolverVisit .= '        if ($this->resolved' . \ucfirst($this->member->getName()) . ' !== null && $this->resolved' . \ucfirst($this->member->getName()) . ' !== false )' . "\n";
        $this->resolverVisit .= "        {\n";
        if ($type->getCollectedType()->getReferencedTypeIfAny() === null)
        {
            $this->resolverVisit .= '            $visitor->resolverVisitResolvedScalarCollectionProperty("' .
                                                $this->member->getName() . '");' . "\n";
        }
        else
        {
            $this->resolverVisit .= '            $visitor->resolverVisitResolvedReferenceCollectionProperty(' .
                '"' . $this->member->getName() . '", "' . $type->getCollectedType()->getReferencedTypeIfAny() . '", ' .
                '$this->resolved' . \ucfirst($this->member->getName()) .");\n";
        }
        $this->resolverVisit .= "        }\n";
        $this->resolverVisit .= '        else' . "\n";
        $this->resolverVisit .= "        {\n";
        $this->resolverVisit .= '            $visitor->resolverVisitUnresolvedCollectionProperty(' .
                                            '"' . $this->member->getName() . '");' . "\n";
        $this->resolverVisit .= "        }\n";

        $this->constructor .= '        $this->' . $this->member->getName() . 'Modifier = ';
        $this->constructor .= 'new \Good\Manners\CollectionModifierStorable();' . "\n";
        $this->constructor .= '        $this->' . $this->member->getName() . '->registerBehaviorModifier(';
        $this->constructor .= '$this->' . $this->member->getName() . 'Modifier);' . "\n";

        $this->classBodyContent .= '    private $' . $this->member->getName() . 'Modifier;' . "\n";
        $this->classBodyContent .= "\n";

        $this->clean .= '        $this->' . $this->member->getName() . 'Modifier->clean();' . "\n";

        $this->markUnresolved .= '        $this->' . $this->member->getName() . 'Modifier->markUnresolved();' . "\n";

        $orderableCheck  = '        if ($this->resolved' . \ucfirst($this->member->getName()) . ' === false)' . "\n";
        $orderableCheck .= "        {\n";
        $orderableCheck .= '            throw new Exception("Unable to order unresolved collection");' . "\n";
        $orderableCheck .= "        }\n";
        $orderableCheck .= "\n";

        $this->isDirty .= "\n";
        $this->isDirty .= '            || $this->' . $this->member->getName() . 'Modifier->isDirty()';

        $this->condition .= '    private $' . $this->member->getName() . ' = null;' . "\n";
        $this->condition .= "\n";
        $this->condition .= '    public function set' . \ucfirst($this->member->getName()) . '(CollectionCondition $condition)' . "\n";
        $this->condition .= "    {\n";
        $this->condition .= '        $problem = $condition->appliesToCollectionType(' . $this->typeDefinition->getName() . '::$' . $this->member->getName() . 'Type);' . "\n";
        $this->condition .= "\n";
        $this->condition .= '        if ($problem !== null)' . "\n";
        $this->condition .= "        {\n";
        $this->condition .= '             throw new \Exception("Condition cannot be applied to this collection: " . $problem);' . "\n";
        $this->condition .= "        }\n";
        $this->condition .= "\n";
        $this->condition .= '        $this->' . $this->member->getName() . ' = $condition;' . "\n";
        $this->condition .= "    }\n";
        $this->condition .= "\n";

        $this->conditionSetterSwitch .= '            case "' . $this->member->getName() . '":' . "\n";
        $this->conditionSetterSwitch .= '                $this->set' . \ucfirst($this->member->getName()) . '($value);' . "\n";
        $this->conditionSetterSwitch .= '                break;' . "\n";
        $this->conditionSetterSwitch .= "\n";

        $this->conditionComplexProcess .= '        if ($this->' . $this->member->getName() . ' !== null)' . "\n";
        $this->conditionComplexProcess .= "        {\n";
        $this->conditionComplexProcess .= '            $processor->processCollectionMember(' . $this->typeDefinition->getName();
        $this->conditionComplexProcess .= '::$' . $this->member->getName() . 'Type, "' . $this->member->getName() . '", $this->' . $this->member->getName() . ');' . "\n";
        $this->conditionComplexProcess .= "        }\n";

        // A bit of a misuse of getReferencedTypeIfAny: if I ever want to remove it, I shouldn't let this get in the way!
        $this->visitNonReference(false, $type->getCollectedType()->getReferencedTypeIfAny() == null, $orderableCheck);
        $this->writeResolvableMemberToResolver($this->member->getName(), $type->getCollectedType()->getReferencedTypeIfAny());
    }

    private function visitNonReference($isScalar, $isOrderable, $orderableCheck = '')
    {
        if ($isScalar)
        {
            $this->resolverVisit .= '        $visitor->resolverVisitScalarProperty("' .
                                                                            $this->member->getName() . '");' . "\n";

            $this->condition .= '    private $' . $this->member->getName() . ' = null;' . "\n";
            $this->condition .= "\n";
            $this->condition .= '    public function set' . \ucfirst($this->member->getName()) . '(Condition $condition)' . "\n";
            $this->condition .= "    {\n";
            $this->condition .= '        $problem = $condition->appliesToType(' . $this->typeDefinition->getName() . '::$' . $this->member->getName() . 'Type);' . "\n";
            $this->condition .= "\n";
            $this->condition .= '        if ($problem !== null)' . "\n";
            $this->condition .= "        {\n";
            $this->condition .= '             throw new \Exception("Condition cannot be applied to this property: " . $problem);' . "\n";
            $this->condition .= "        }\n";
            $this->condition .= "\n";
            $this->condition .= '        $this->' . $this->member->getName() . ' = $condition;' . "\n";
            $this->condition .= "    }\n";
            $this->condition .= "\n";

            $this->conditionSetterSwitch .= '            case "' . $this->member->getName() . '":' . "\n";
            $this->conditionSetterSwitch .= '                $this->set' . \ucfirst($this->member->getName());
            $this->conditionSetterSwitch .= '($value instanceof Condition ? $value : new EqualTo($value));' . "\n";
            $this->conditionSetterSwitch .= '                break;' . "\n";
            $this->conditionSetterSwitch .= "\n";

            $this->conditionComplexProcess .= '        if ($this->' . $this->member->getName() . ' !== null)' . "\n";
            $this->conditionComplexProcess .= "        {\n";
            $this->conditionComplexProcess .= '            $processor->processMember(' . $this->typeDefinition->getName();
            $this->conditionComplexProcess .= '::$' . $this->member->getName() . 'Type, "' . $this->member->getName() . '", $this->' . $this->member->getName() . ');' . "\n";
            $this->conditionComplexProcess .= "        }\n";
        }

        if ($isOrderable)
        {
            $this->resolver .= '    private $orderNumber' . \ucfirst($this->member->getName()) . ' = -1;' . "\n";
            $this->resolver .= '    private $orderDirection' . \ucfirst($this->member->getName()) . ' = -1;' . "\n";
            $this->resolver .= "    \n";
            $this->resolver .= '    public function orderBy' . \ucfirst($this->member->getName()) . 'Asc()' . "\n";
            $this->resolver .= "    {\n";
            $this->resolver .= $orderableCheck;
            $this->resolver .= '        $this->orderNumber' . \ucfirst($this->member->getName()) .
                                                            ' = $this->drawOrderTicket();' . "\n";
            $this->resolver .= '        $this->orderDirection' . \ucfirst($this->member->getName()) .
                                                            ' = self::ORDER_DIRECTION_ASC;' . "\n";
            $this->resolver .= '        return $this;' . "\n";
            $this->resolver .= "    }\n";
            $this->resolver .= "    \n";
            $this->resolver .= '    public function orderBy' . \ucfirst($this->member->getName()) . 'Desc()' . "\n";
            $this->resolver .= "    {\n";
            $this->resolver .= $orderableCheck;
            $this->resolver .= '        $this->orderNumber' . \ucfirst($this->member->getName()) .
                                                            ' = $this->drawOrderTicket();' . "\n";
            $this->resolver .= '        $this->orderDirection' . \ucfirst($this->member->getName()) .
                                                            ' = self::ORDER_DIRECTION_DESC;' . "\n";
            $this->resolver .= '        return $this;' . "\n";
            $this->resolver .= "    }\n";
            $this->resolver .= "    \n";

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
        }

        $this->debugInfo .= '            "' . $this->member->getName() . '" => $this->' . $this->member->getName() . ',' . "\n";
        $this->debugInfo .= '            "is' . \ucfirst($this->member->getName()) . 'Dirty" => $this->is' . \ucfirst($this->member->getName()) . 'Dirty,' . "\n";

        $this->isDirty .= "\n";
        $this->isDirty .= '            || $this->is' . \ucfirst($this->member->getName()) . 'Dirty';
    }

    public function writeResolvableMemberToResolver($memberName, $resolveType)
    {
        $this->resolver .= '    private $resolved' . \ucfirst($memberName) . ' = ';

        if ($resolveType === null)
        {
            $this->resolver .= 'false;';
        }
        else {
            $this->resolver .= 'null;';
        }

        $this->resolver .= "\n";
        $this->resolver .= "    \n";
        $this->resolver .= '    public function resolve' . \ucfirst($memberName) . '()' . "\n";
        $this->resolver .= "    {\n";
        $this->resolver .= '        $this->resolved' . \ucfirst($memberName) . ' = ';

        if ($resolveType === null)
        {
            $this->resolver .= "true;\n";
        }
        else
        {
            $this->resolver .= 'new ' . $resolveType . 'Resolver($this->root);' . "\n";
        }

        $this->resolver .= "        \n";

        if ($resolveType === null)
        {
            $this->resolver .= '        return $this;' . "\n";
        }
        else
        {
            $this->resolver .= '        return $this->resolved' . \ucfirst($memberName) . ";\n";
        }

        $this->resolver .= "    }\n";
        $this->resolver .= "    \n";
        $this->resolver .= '    public function get' . \ucfirst($memberName) . '()' . "\n";
        $this->resolver .= "    {\n";
        $this->resolver .= '        return $this->resolved' . \ucfirst($memberName) . ';' . "\n";
        $this->resolver .= "    }\n";
        $this->resolver .= "    \n";
    }

    public function constructor()
    {
        return $this->constructor;
    }

    public function extraFiles()
    {
        return $this->extraFiles;

        $this->extraFiles = array();
    }
}

?>
