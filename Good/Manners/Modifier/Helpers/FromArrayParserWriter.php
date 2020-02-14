<?php

namespace Good\Manners\Modifier\Helpers;

use Good\Rolemodel\TypeVisitor;
use Good\Rolemodel\Schema\Type;
use Good\Rolemodel\Schema\Type\CollectionType;
use Good\Rolemodel\Schema\Type\DateTimeType;
use Good\Rolemodel\Schema\Type\FloatType;
use Good\Rolemodel\Schema\Type\IntType;
use Good\Rolemodel\Schema\Type\ReferenceType;
use Good\Rolemodel\Schema\Type\TextType;

class FromArrayParserWriter implements TypeVisitor
{
    private $fromArrayParser;

    public function writeFromArrayParser(Type $type)
    {
        $this->fromArrayParser = null;
        $type->acceptTypeVisitor($this);

        return $this->fromArrayParser;
    }

    public function visitDateTimeType(DateTimeType $type)
    {
        $this->fromArrayParser  = '$value === null || $value instanceof \DateTimeImmutable ? ';
        $this->fromArrayParser .= '$value : new DateTimeImmutable($value, new DateTimeZone("UTC"))';
    }

    public function visitIntType(IntType $type)
    {
        $this->fromArrayParser = '\intval($value)';
    }

    public function visitFloatType(FloatType $type)
    {
        $this->fromArrayParser = '\floatval($value)';
    }

    public function visitReferenceType(ReferenceType $type)
    {
        $this->fromArrayParser = '$value';
    }

    public function visitTextType(TextType $type)
    {
        $this->fromArrayParser = '\strval($value)';
    }

    public function visitCollectionType(CollectionType $type)
    {
        $subParser = $this->writeFromArrayParser($type->getCollectedType());

        $this->fromArrayParser  = 'array_map(function ($value) {return ';
        $this->fromArrayParser  .= $subParser;
        $this->fromArrayParser  .= ';}, $value)';
    }
}
?>
