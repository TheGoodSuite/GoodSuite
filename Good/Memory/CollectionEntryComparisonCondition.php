<?php

namespace Good\Memory;

use Good\Manners\Comparison;
use Good\Manners\Condition;
use Good\Manners\Condition\ComplexCondition;
use Good\Manners\Processors\ConditionProcessor;
use Good\Manners\Processors\ComplexConditionProcessor;
use Good\Rolemodel\Schema\Type;
use Good\Rolemodel\Schema\Type\CollectionType;
use Good\Rolemodel\Schema\Type\DateTimeType;
use Good\Rolemodel\Schema\Type\FloatType;
use Good\Rolemodel\Schema\Type\IntType;
use Good\Rolemodel\Schema\Type\ReferenceType;
use Good\Rolemodel\Schema\Type\TextType;
use Good\Rolemodel\TypeVisitor;

class CollectionEntryComparisonCondition implements ComplexCondition, TypeVisitor
{
    private $collectedType;
    private $comparison;

    private $conditionProcessor;

    public function __construct(Type $collectedType, Condition $comparison)
    {
        $this->collectedType = $collectedType;
        $this->comparison = $comparison;
    }

    public function processCondition(ConditionProcessor $processor)
    {
        $processor->processComplexCondition($this);
    }

    public function processComplexCondition(ComplexConditionProcessor $processor)
    {
        $this->conditionProcessor = $processor;

        $this->collectedType->acceptTypeVisitor($this);
    }

    public function visitCollectionType(CollectionType $type)
    {
        throw new \Exception("Collections of Collection are not supported at the moment.");
    }

    public function  visitDateTimeType(DateTimeType $type)
    {
        $this->conditionProcessor->processPrimitiveMember($type, "value", $this->comparison);
    }

    public function visitFloatType(FloatType $type)
    {
        $this->conditionProcessor->processPrimitiveMember($type, "value", $this->comparison);
    }

    public function visitIntType(IntType $type)
    {
        $this->conditionProcessor->processPrimitiveMember($type, "value", $this->comparison);
    }

    public function visitTextType(TextType $type)
    {
        $this->conditionProcessor->processPrimitiveMember($type, "value", $this->comparison);
    }

    public function visitReferenceType(ReferenceType $type)
    {
        $this->conditionProcessor->processReferenceMemberAsComparison($type, "value", $this->comparison);
    }

    public function getTargetType()
    {
        return null;
    }
}

?>
