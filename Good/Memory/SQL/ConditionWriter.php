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
use Good\Manners\ConditionProcessor;
use Good\Manners\CollectionComparisonProcessor;
use Good\Manners\Comparison\Collection\CollectionComparison;
use Good\Manners\Comparison;
use Good\Manners\Comparison\EqualityComparison;

class ConditionWriter implements ConditionProcessor, CollectionComparisonProcessor
{
    private $storage;
    private $condition;
    private $first;

    private $currentTable;
    private $currentTableName;

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

    public function writeCondition(Condition $condition)
    {
        $this->first = true;
        $this->condition = '';

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

    public function processStorableConditionId(EqualityComparison $comparison)
    {
        $this->writeBracketOrAnd();

        $field = '`t' . $this->currentTable . '`.`id`';
        $fragmentWriter = new IntFragmentWriter($this->storage, $field);

        $this->condition .= $fragmentWriter->writeFragment($comparison);
    }

    public function processStorableConditionReferenceAsCondition($name, $datatypeName, $condition)
    {
        $this->writeBracketOrAnd();

        $join = $this->storage->getJoin($this->currentTable, $name);

        if ($join == -1)
        {
            $join = $this->storage->createJoin($this->currentTable, $name, $datatypeName, 'id');
        }

        $subWriter = new ConditionWriter($this->storage, $join, $datatypeName);
        $subWriter->writeCondition($condition);

        $this->condition .= $subWriter->getCondition();
    }

    public function processStorableConditionReferenceAsComparison($name, EqualityComparison $comparison)
    {
        $this->writeBracketOrAnd();

        $field = '`t' . $this->currentTable . '`.`' . $this->storage->fieldNamify($name) . '`';
        $fragmentWriter = new ReferenceFragmentWriter($field);

        $this->condition .= $fragmentWriter->writeFragment($comparison);
    }

    public function processStorableConditionText($name, Comparison $comparison)
    {
        $this->writeBracketOrAnd();

        $field = '`t' . $this->currentTable . '`.`' . $this->storage->fieldNamify($name) . '`';
        $fragmentWriter = new TextFragmentWriter($this->storage, $field);

        $this->condition .= $fragmentWriter->writeFragment($comparison);
    }

    public function processStorableConditionInt($name, Comparison $comparison)
    {
        $this->writeBracketOrAnd();

        $field = '`t' . $this->currentTable . '`.`' . $this->storage->fieldNamify($name) . '`';
        $fragmentWriter = new IntFragmentWriter($this->storage, $field);

        $this->condition .= $fragmentWriter->writeFragment($comparison);
    }

    public function processStorableConditionFloat($name, Comparison $comparison)
    {
        $this->writeBracketOrAnd();

        $field = '`t' . $this->currentTable . '`.`' . $this->storage->fieldNamify($name) . '`';
        $fragmentWriter = new FloatFragmentWriter($this->storage, $field);

        $this->condition .= $fragmentWriter->writeFragment($comparison);
    }

    public function processStorableConditionDateTime($name, Comparison $comparison)
    {
        $this->writeBracketOrAnd();

        $field = '`t' . $this->currentTable . '`.`' . $this->storage->fieldNamify($name) . '`';
        $fragmentWriter = new DateTimeFragmentWriter($this->storage, $field);

        $this->condition .= $fragmentWriter->writeFragment($comparison);
    }

    private $collectionName;

    public function processStorableConditionCollection($name, CollectionComparison $comparison)
    {
        $this->collectionName = $name;

        $comparison->processCollectionComparison($this);
    }

    public function processHasAConditionComparison(Condition $condition)
    {
        $this->processHasAComparison(new CollectionEntryConditionCondition($condition));
    }

    public function processHasAComparisonComparison(Comparison $comparison)
    {
        $this->processHasAComparison(new CollectionEntryComparisonCondition($comparison));
    }

    public function processHasOnlyConditionComparison(Condition $condition)
    {
        $this->processHasOnlyComparison(new CollectionEntryConditionCondition($condition));
    }

    public function processHasOnlyComparisonComparison(Comparison $comparison)
    {
        $this->processHasOnlyComparison(new CollectionEntryComparisonCondition($comparison));
    }

    private function processHasAComparison($collectionEntryCondition)
    {
        $this->writeBracketOrAnd();

        $table = $this->currentTableName . '_' . $name;
        $join = $this->storage->createJoin($this->currentTable, $name, $table, 'owner', null, false);

        $subWriter = new ConditionWriter($this->storage, $join, $currentTableName);
        $subWriter->writeCondition($collectionEntryCondition);

        $this->condition .= $subWriter->getCondition();
        $this->condition .= " AND COUNT(`t" . $join . "`.`value`) > 0";
    }

    private function processHasOnlyComparison($collectionEntryCondition)
    {
        $this->writeBracketOrAnd();

        $table = $this->currentTableName . '_' . $name;
        $join = $this->storage->createJoin($this->currentTable, $name, $table, 'owner', null, false);

        $subWriter = new ConditionWriter($this->storage, $join, $currentTableName);
        $subWriter->writeCondition($collectionEntryCondition);

        $secondJoin = $this->storage->createJoin($this->currentTable, $name, $table, 'owner', null, false);

        $this->condition .= $subWriter->getCondition();
        $this->condition .= " AND COUNT(`t" . $join . "`.`value`) = COUNT(`t" . $secondJoin . "`.`value`)";
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
