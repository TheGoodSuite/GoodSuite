<?php

namespace Good\Manners\Modifier\Helpers;

use Good\Rolemodel\TypeVisitor;
use Good\Rolemodel\Schema\Member;
use Good\Rolemodel\Schema\Type;
use Good\Rolemodel\Schema\Type\CollectionType;
use Good\Rolemodel\Schema\Type\DateTimeType;
use Good\Rolemodel\Schema\Type\BooleanType;
use Good\Rolemodel\Schema\Type\FloatType;
use Good\Rolemodel\Schema\Type\IntType;
use Good\Rolemodel\Schema\Type\ReferenceType;
use Good\Rolemodel\Schema\Type\TextType;

class ToArrayFormatterWriter implements TypeVisitor
{
    private $toArrayFormatter;
    private $formattable;

    public function writeToArrayFormatter($formattable, Type $type)
    {
        $this->toArrayFormatter = null;
        $this->formattable = $formattable;

        $type->acceptTypeVisitor($this);

        return $this->toArrayFormatter;
    }

    public function visitDateTimeType(DateTimeType $type)
    {
        $this->toArrayFormatter  = '$datesToIso && ' . $this->formattable . ' != null ? ';
        $this->toArrayFormatter .= $this->formattable . '->format(\DateTimeImmutable::ATOM) : ' . $this->formattable;
    }

    public function visitBooleanType(BooleanType $type)
    {
        $this->toArrayFormatter = $this->formattable;
    }

    public function visitIntType(IntType $type)
    {
        $this->toArrayFormatter = $this->formattable;
    }

    public function visitFloatType(FloatType $type)
    {
        $this->toArrayFormatter = $this->formattable;
    }

    public function visitReferenceType(ReferenceType $type)
    {
        $this->toArrayFormatter  = $this->formattable . ' == null ? null : ';
        $this->toArrayFormatter .= $this->formattable . '->toArray($datesToIso)';
    }

    public function visitTextType(TextType $type)
    {
        $this->toArrayFormatter = $this->formattable;
    }

    public function visitCollectionType(CollectionType $type)
    {
        // Save it because it won't survive the subFormatter call
        $formattable = $this->formattable;

        $subFormatter = $this->writeToArrayFormatter('$value', $type->getCollectedType());

        $this->toArrayFormatter  = 'array_map(function ($value) use ($datesToIso) {return ';
        $this->toArrayFormatter  .= $subFormatter;
        $this->toArrayFormatter  .= ';}, ' . $formattable . '->toArray())';
    }
}
?>
