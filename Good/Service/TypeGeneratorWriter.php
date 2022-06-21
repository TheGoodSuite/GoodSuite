<?php

namespace Good\Service;

use \Good\Rolemodel\TypeVisitor;
use \Good\Rolemodel\Schema;
use \Good\Rolemodel\Schema\Type;

class TypeGeneratorWriter implements TypeVisitor
{
    private $typeGenerator;
    private $memberName;

    public function getTypeGenerator(Type $type, $memberName)
    {
        $this->memberName = $memberName;

        $type->acceptTypeVisitor($this);

        return $this->typeGenerator;
    }

    public function visitDateTimeType(Schema\Type\DateTimeType $type)
    {
        $this->typeGenerator = 'new \\Good\\Service\\Type\\DateTimeType(';
        $this->typeGenerator .= '[], "' . $this->memberName . '")';
    }

    public function visitBooleanType(Schema\Type\BooleanType $type)
    {
        $this->typeGenerator = 'new \\Good\\Service\\Type\\BooleanType(';
        $this->typeGenerator .= '[], "' . $this->memberName . '")';
    }

    public function visitIntType(Schema\Type\IntType $type)
    {
        $typeModifiers = $type->getTypeModifiers();

        $this->typeGenerator  = 'new \\Good\\Service\\Type\\IntType([';
        $this->typeGenerator .= '"minValue" => ' . $typeModifiers['minValue'];
        $this->typeGenerator .= ', "maxValue" => ' . $typeModifiers['maxValue'] . '],';
        $this->typeGenerator .= '"' . $this->memberName . '")';
    }

    public function visitFloatType(Schema\Type\FloatType $type)
    {
        $this->typeGenerator = 'new \\Good\\Service\\Type\\FloatType(';
        $this->typeGenerator .= '[], "' . $this->memberName . '")';
    }

    public function visitReferenceType(Schema\Type\ReferenceType $type)
    {
        $this->typeGenerator = 'new \\Good\\Service\\Type\\ReferenceType("' . $type->getReferencedType() . '")';
    }

    public function visitTextType(Schema\Type\TextType $type)
    {
        $typeModifiers = $type->getTypeModifiers();

        $this->typeGenerator  = 'new \\Good\\Service\\Type\\TextType([';
        $this->typeGenerator .= '"minLength" => ' . $typeModifiers['minLength'];

        if (array_key_exists('maxLength', $typeModifiers))
        {
            $this->typeGenerator .= ', "maxLength" => ' . $typeModifiers['maxLength'];
        }

        $this->typeGenerator .= '], "' . $this->memberName . '")';
    }

    public function visitCollectionType(Schema\Type\CollectionType $type)
    {
        $subGenerator = $this->getTypeGenerator($type->getCollectedType(), $this->memberName);

        $this->typeGenerator = 'new \\Good\\Service\\Type\\CollectionType(' . $subGenerator . ')';
    }
}

?>
