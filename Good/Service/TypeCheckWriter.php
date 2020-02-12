<?php

namespace Good\Service;

use \Good\Rolemodel\TypeVisitor;
use \Good\Rolemodel\Schema;
use \Good\Rolemodel\Schema\Type;

class TypeCheckWriter implements TypeVisitor
{
    private $typeCheck;

    public function getTypeCheck(Type $type)
    {
        $type->acceptTypeVisitor($this);

        return $this->typeCheck;
    }

    public function visitDateTimeType(Schema\Type\DateTimeType $type)
    {
        $this->typeCheck = '\\Good\\Service\\TypeChecker::checkDateTime($value)';
    }

    public function visitIntType(Schema\Type\IntType $type)
    {
        $typeModifiers = $type->getTypeModifiers();

        $this->typeCheck = '\\Good\\Service\\TypeChecker::checkInt($value, ' . $typeModifiers['minValue'];
        $this->typeCheck .= ', ' . $typeModifiers['maxValue'] . ')';
    }

    public function visitFloatType(Schema\Type\FloatType $type)
    {
        $this->typeCheck = '\\Good\\Service\\TypeChecker::checkFloat($value)';
    }

    public function visitReferenceType(Schema\Type\ReferenceType $type)
    {
        $this->typeCheck = null;
    }

    public function visitTextType(Schema\Type\TextType $type)
    {
        $typeModifiers = $type->getTypeModifiers();

        $this->typeCheck = '\\Good\\Service\\TypeChecker::checkString($value, ' . $typeModifiers['minLength'];

        if (array_key_exists('maxLength', $typeModifiers))
        {
            $this->typeCheck .= ', ' . $typeModifiers['maxLength'];
        }

        $this->typeCheck .= ')';
    }

    public function visitCollectionType(Schema\Type\CollectionType $type)
    {
        $this->typeCheck = null;
    }
}

?>
