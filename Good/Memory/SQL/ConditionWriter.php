<?php

namespace Good\Memory\SQL;

use Good\Memory\SQLStorage;
use Good\Memory\SQL\ConditionWriter\DateTimeFragmentWriter;
use Good\Memory\SQL\ConditionWriter\FloatFragmentWriter;
use Good\Memory\SQL\ConditionWriter\IntFragmentWriter;
use Good\Memory\SQL\ConditionWriter\ReferenceFragmentWriter;
use Good\Memory\SQL\ConditionWriter\TextFragmentWriter;
use Good\Manners\Storable;
use Good\Manners\Condition;
use Good\Manners\ConditionProcessor;
use Good\Manners\Comparison;
use Good\Manners\Comparison\EqualityComparison;

class ConditionWriter implements ConditionProcessor
{
    private $storage;
    private $condition;
    private $first;

    private $currentTable;

    public function __construct(SQLStorage $storage, $currentTable)
    {
        $this->storage = $storage;
        $this->currentTable = $currentTable;
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

        $subWriter = new ConditionWriter($this->storage, $join);
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
