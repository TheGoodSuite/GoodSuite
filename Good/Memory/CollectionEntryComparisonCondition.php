<?php

namespace Good\Memory;

use Good\Manners\Comparison;
use Good\Manners\Condition;
use Good\Manners\ConditionProcessor;
use Good\Rolemodel\Schema\Type;
use Good\Rolemodel\Schema\Type\CollectionType;
use Good\Rolemodel\Schema\Type\DateTimeType;
use Good\Rolemodel\Schema\Type\FloatType;
use Good\Rolemodel\Schema\Type\IntType;
use Good\Rolemodel\Schema\Type\ReferenceType;
use Good\Rolemodel\Schema\Type\TextType;
use Good\Rolemodel\TypeVisitor;

class CollectionEntryComparisonCondition implements Condition, TypeVisitor
{
    private $collectedType;
    private $comparison;

    private $conditionProcessor;

    public function __construct(Type $collectedType, Comparison $comparison)
    {
        $this->collectedType = $collectedType;
        $this->comparison = $comparison;
    }

    public function processCondition(ConditionProcessor $processor)
    {
        $this->processor = $processor;

        $this->collectedType->acceptTypeVisitor($this);
    }

    public function visitCollectionType(CollectionType $type)
    {
        throw new \Exception("Collections of Collection are not supported at the moment.");
    }

    public function  visitDateTimeType(DateTimeType $type)
    {
        $this->conditionProcessor->processStorableConditionDateTime("value", $this->comparison);
    }

    public function visitFloatType(FloatType $type)
    {
        $this->storableVisitor->processStorableConditionFloat("value", $this->comparison);
    }

    public function visitIntType(IntType $type)
    {
        $this->storableVisitor->processStorableConditionInt("value", $this->comparison);
    }

    public function visitReferenceType(ReferenceType $type)
    {
        $this->storableVisitor->processStorableConditionReferenceAsComparison("value", $this->comparison);
    }

    public function visitTextType(TextType $type)
    {
        $this->storableVisitor->processStorableConditionText("value", $this->comparison);
    }

    public function getTargetType()
    {
        return null;
    }
}

?>
