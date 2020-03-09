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

class CollectionEntryConditionCondition implements Condition, TypeVisitor
{
    private $collectedType;
    private $condition;

    private $conditionProcessor;

    public function __construct(Type $collectedType, Condition $condition)
    {
        $this->collectedType = $collectedType;
        $this->condition = $condition;
    }

    public function processCondition(ConditionProcessor $processor)
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
        throw new \Exception("Invalid");
    }

    public function visitFloatType(FloatType $type)
    {
        throw new \Exception("Invalid");
    }

    public function visitIntType(IntType $type)
    {
        throw new \Exception("Invalid");
    }

    public function visitReferenceType(ReferenceType $type)
    {
        $this->conditionProcessor->processStorableConditionReferenceAsCondition("value", $type->getReferencedType(), $this->condition);
    }

    public function visitTextType(TextType $type)
    {
        throw new \Exception("Invalid");
    }

    public function getTargetType()
    {
        return null;
    }
}

?>
