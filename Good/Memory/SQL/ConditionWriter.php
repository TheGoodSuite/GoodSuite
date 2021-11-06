<?php

namespace Good\Memory\SQL;

use Good\Memory\SQLStorage;
use Good\Memory\CollectionEntryComparisonCondition;
use Good\Memory\CollectionEntryConditionCondition;
use Good\Memory\SQL\ConditionWriter\DateTimeFragmentWriter;
use Good\Memory\SQL\ConditionWriter\FloatFragmentWriter;
use Good\Memory\SQL\ConditionWriter\IntFragmentWriter;
use Good\Memory\SQL\ConditionWriter\ReferenceFragmentWriter;
use Good\Memory\SQL\ConditionWriter\TextFragmentWriter;
use Good\Manners\Storable;
use Good\Manners\Condition;
use Good\Manners\Condition\ComplexCondition;
use Good\Manners\ComplexConditionProcessor;
use Good\Manners\ConditionProcessor;
use Good\Manners\CollectionComparisonProcessor;
use Good\Manners\Condition\Collection\CollectionCondition;
use Good\Rolemodel\TypeVisitor;
use Good\Rolemodel\Schema\Type\ReferenceType;
use Good\Rolemodel\Schema\Type\TextType;
use Good\Rolemodel\Schema\Type\IntType;
use Good\Rolemodel\Schema\Type\FloatType;
use Good\Rolemodel\Schema\Type\DateTimeType;
use Good\Rolemodel\Schema\Type\CollectionType;
use Good\Service\Type;

class ConditionWriter implements ComplexConditionProcessor, ConditionProcessor, CollectionComparisonProcessor, TypeVisitor
{
    private $storage;
    private $condition;
    private $having;
    private $first;

    private $currentTable;
    private $currentTableName;

    private $fieldName;
    private $comparison;

    public function __construct(SQLStorage $storage, $currentTable, $currentTableName)
    {
        $this->storage = $storage;
        $this->currentTable = $currentTable;
        $this->currentTableName = $currentTableName;
    }

    public function getCondition()
    {
        return $this->condition;
    }

    public function getHaving()
    {
        return $this->having;
    }

    public function writeCondition(Condition $condition)
    {
        $this->first = true;
        $this->condition = '';
        $this->having = [];

        $condition->processCondition($this);

        if ($this->first)
        {
            $this->condition = '1 = 1';
        }
    }

    public function processAndCondition(Condition $condition1, Condition $condition2)
    {
        $this->writeCondition($condition1);
        $sqlCondition1 = $this->getCondition();

        $this->writeCondition($condition2);
        $sqlCondition2 = $this->getCondition();

        $this->condition = '(' . $sqlCondition1 . ' AND ' . $sqlCondition2 . ')';
    }

    public function processOrCondition(Condition $condition1, Condition $condition2)
    {
        $this->writeCondition($condition1);
        $sqlCondition1 = $this->getCondition();

        $this->writeCondition($condition2);
        $sqlCondition2 = $this->getCondition();

        $this->condition = '(' . $sqlCondition1 . ' OR ' . $sqlCondition2 . ')';
    }

    public function processEqualToCondition($value)
    {
        throw new \Exception("Using equal to as a query condition has not yet been implemented");
    }

    public function processNotEqualToCondition($value)
    {
        throw new \Exception("Using not equal to as a query condition has not yet been implemented");
    }

    public function processGreaterThanCondition($value)
    {
        throw new \Exception("Greater than cannot be used as a query condition");
    }

    public function processGreaterOrEqualCondition($value)
    {
        throw new \Exception("Greater than or equal cannot be used as a query condition");
    }

    public function processLessThanCondition($value)
    {
        throw new \Exception("Less than cannot be used as a query condition");
    }

    public function processLessOrEqualCondition($value)
    {
        throw new \Exception("Less than or equal cannot be used as a query condition");
    }

    public function processComplexCondition(ComplexCondition $condition)
    {
        $condition->processComplexCondition($this);
    }

    public function processId(Condition $comparison)
    {
        $this->writeBracketOrAnd();

        $field = '`t' . $this->currentTable . '`.`id`';
        $fragmentWriter = new IntFragmentWriter($this->storage);

        $this->condition .= $fragmentWriter->writeFragment($comparison, $field);
    }

    public function processReferenceMemberAsCondition(ReferenceType $type, $name, $condition)
    {
        $this->writeBracketOrAnd();

        $join = $this->storage->getJoin($this->currentTable, $name);

        if ($join == -1)
        {
            $join = $this->storage->createJoin($this->currentTable, $name, $type->getReferencedType(), 'id');
        }

        $subWriter = new ConditionWriter($this->storage, $join, $type->getReferencedType());
        $subWriter->writeCondition($condition);

        $this->condition .= $subWriter->getCondition();
        array_push($this->having, ...$subWriter->getHaving());
    }

    public function processReferenceMemberAsComparison(ReferenceType $type, $name, Condition $comparison)
    {
        $this->writeBracketOrAnd();

        $field = '`t' . $this->currentTable . '`.`' . $this->storage->fieldNamify($name) . '`';
        $fragmentWriter = new ReferenceFragmentWriter();

        $this->condition .= $fragmentWriter->writeFragment($comparison, $field);
    }

    public function processPrimitiveMember(Type $type, $name, Condition $comparison)
    {
        $this->fieldName = $name;
        $this->comparison = $comparison;

        $type->acceptTypeVisitor($this);
    }

    public function visitReferenceType(ReferenceType $type)
    {
        throw new \Exception("Not supported");
    }

    public function visitTextType(TextType $type)
    {
        $fragmentWriter = new TextFragmentWriter($this->storage);

        $this->writeFragment($fragmentWriter);
    }

    public function visitIntType(IntType $type)
    {
        $fragmentWriter = new IntFragmentWriter($this->storage);

        $this->writeFragment($fragmentWriter);
    }

    public function visitFloatType(FloatType $type)
    {
        $fragmentWriter = new FloatFragmentWriter($this->storage);

        $this->writeFragment($fragmentWriter);
    }

    public function visitDateTimeType(DateTimeType $type)
    {
        $fragmentWriter = new DateTimeFragmentWriter($this->storage);

        $this->writeFragment($fragmentWriter);
    }

    public function visitCollectionType(CollectionType $type)
    {
        throw new \Exception("Not supported");
    }

    private function writeFragment($fragmentWriter)
    {
        $this->writeBracketOrAnd();

        $field = '`t' . $this->currentTable . '`.`' . $this->storage->fieldNamify($this->fieldName) . '`';

        $this->condition .= $fragmentWriter->writeFragment($this->comparison, $field);
    }

    private $collectionName;
    private $type;

    public function processCollectionMember(CollectionType $type, $name, CollectionCondition $comparison)
    {
        $this->collectionName = $name;
        $this->type = $type;

        $comparison->processCollectionComparison($this);
    }

    public function processHasAConditionComparison(Condition $condition)
    {
        $this->processHasAComparison(new CollectionEntryConditionCondition($this->type->getCollectedType(), $condition));
    }

    public function processHasAComparisonComparison(Condition $comparison)
    {
        $this->processHasAComparison(new CollectionEntryComparisonCondition($this->type->getCollectedType(), $comparison));
    }

    public function processHasOnlyConditionComparison(Condition $condition)
    {
        $this->processHasOnlyComparison(new CollectionEntryConditionCondition($this->type->getCollectedType(), $condition));
    }

    public function processHasOnlyComparisonComparison(Condition $comparison)
    {
        $this->processHasOnlyComparison(new CollectionEntryComparisonCondition($this->type->getCollectedType(), $comparison));
    }

    private function processHasAComparison($collectionEntryCondition)
    {
        $this->writeBracketOrAnd();

        $table = $this->currentTableName . '_' . $this->collectionName;
        $join = $this->storage->createJoin($this->currentTable, 'id', $table, 'owner', null, false);

        $subWriter = new ConditionWriter($this->storage, $join, $this->currentTableName);
        $subWriter->writeCondition($collectionEntryCondition);

        $this->condition .= $subWriter->getCondition();
        array_push($this->having, ...$subWriter->getHaving());
    }

    private function processHasOnlyComparison($collectionEntryCondition)
    {
        $this->writeBracketOrAnd();

        $table = $this->currentTableName . '_' . $this->collectionName;
        $join = $this->storage->createJoin($this->currentTable, 'id', $table, 'owner', null, false);

        $subWriter = new ConditionWriter($this->storage, $join, $this->currentTableName);
        $subWriter->writeCondition($collectionEntryCondition);

        $secondJoin = $this->storage->createJoin($this->currentTable, 'id', $table, 'owner', null, false);

        $this->having[] = "COUNT(DISTINCT `t" . $join . "`.`value`) = COUNT(DISTINCT `t" . $secondJoin . "`.`value`)";

        $this->condition .= '(' . $subWriter->getCondition() . ' OR `t' . $join . '`.`owner` IS NULL)';
        array_push($this->having, ...$subWriter->getHaving());
    }

    private function writeBracketOrAnd()
    {
        if ($this->first)
        {
            // removed brackets change name of function?
            //$this->condition = '(';
            $this->first = false;
        }
        else
        {
            $this->condition .= ' AND ';
        }
    }
}

?>
