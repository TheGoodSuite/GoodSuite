<?php

namespace Good\Memory\SQL;

use Good\Memory\SQLStorage;
use Good\Memory\SQL\ConditionWriter\DateTimeFragmentWriter;
use Good\Memory\SQL\ConditionWriter\FloatFragmentWriter;
use Good\Memory\SQL\ConditionWriter\IntFragmentWriter;
use Good\Memory\SQL\ConditionWriter\ReferenceFragmentWriter;
use Good\Memory\SQL\ConditionWriter\TextFragmentWriter;
use Good\Manners\Storable;
use Good\Manners\StorableVisitor;
use Good\Manners\Comparison\EqualityComparison;
use Good\Manners\Condition;
use Good\Manners\ConditionProcessor;

$started = false;

class UpdateConditionWriter implements ConditionProcessor
{
    private $storage;
    private $condition;
    private $first;

    private $currentTable;
    private $to;

    private $updatingTableNumber;
    private $updatingTableCondition;
    private $updatingTableName;

    private $joining;
    private $joinedTables;
    private $phase2;
    private $rootTableName;

    public function __construct(SQLStorage $storage, $currentTable)
    {
        $this->storage = $storage;
        $this->currentTable = $currentTable;
    }

    public function getCondition()
    {
        return $this->condition;
    }

    private function getJoining()
    {
        return $this->joining;
    }

    public function writeCondition(Condition $condition,
                                   $rootTableName,
                                   $updatingTableNumber,
                                   $updatingTableName)
    {
        $this->updatingTableNumber = $updatingTableNumber;
        $this->updatingTableName = $updatingTableName;
        $this->rootTableName = $rootTableName;

        $this->first = true;

        $this->condition = '';

        if ($this->updatingTableNumber == $this->currentTable)
        {
            $this->updatingTableCondition = $condition;
        }
        else
        {
            $this->phase2 = false;
            $this->writeSimpleCondition($condition);

            $joins = '';

            if ($this->updatingTableCondition == null)
            {
                $join = $this->storage->getReverseJoin($this->updatingTableNumber);

                while ($join->tableNumberOrigin != 0)
                {
                    $join = $this->storage->getReverseJoin($join->tableNumberOrigin);

                    $sql = ' JOIN `' . $this->storage->tableNamify($join->tableNameDestination) .
                                                    '` AS `t' . $join->tableNumberDestination . '`';
                    $sql .= ' ON `t' . $join->tableNumberOrigin . '`.`' .
                                        $this->storage->fieldNamify($join->fieldNameOrigin) . '`';
                    $sql .= ' = `t' . $join->tableNumberDestination . '`.`id`';

                    // They need to be added to the sql in reverse as well, or else
                    // we'll get unknown table names
                    $joins = $sql . $joins;
                }
            }

            $join = $this->storage->getReverseJoin($this->updatingTableNumber);

            $sql  = '`' . $this->storage->tableNamify($join->tableNameDestination) . '`.`id`';
            $sql .= ' IN (SELECT `t' . $join->tableNumberOrigin . '`.`' .
                            $this->storage->fieldNamify($join->fieldNameOrigin) . '`';
            $sql .= ' FROM `' . $this->storage->tableNamify($this->rootTableName) . '` AS `t0`';

            $sql .= $joins;
            $sql .= ' WHERE ' . $this->condition;

            $sql .= ')';
            $this->writeBracketOrAnd();
            $this->condition = $sql;
        }

        // If the Table isn't in our $to, so we don't have to care about doing the
        // part of $it's tree after it either
        if ($this->updatingTableCondition != null)
        {
            $this->tableName = $this->storage->tableNamify($this->updatingTableName);
            $this->phase2 = true;
            $this->writeBracketOrAnd();
            $this->first = true;
            $this->currentTable = $this->updatingTableNumber;

            $this->updatingTableCondition->processCondition($this);
        }

        if ($this->first)
        {
            $this->condition = '1 = 1';
        }
    }

    public function writeSimpleCondition(Condition $condition)
    {
        $this->first = true;
        $this->condition = '';
        $this->joining = '';
        $this->updatingTableFound = null;

        $condition->processCondition($this);

        if ($this->first)
        {
            $this->condition = '1 = 1';
        }
    }

    public function processAndCondition(Condition $condition1, Condition $condition2)
    {
        $this->writeCondition($condition1,
                              $this->rootTableName,
                              $this->updatingTableNumber,
                              $this->updatingTableName);
        $sqlCondition1 = $this->getCondition();

        $this->writeCondition($condition2,
                              $this->rootTableName,
                              $this->updatingTableNumber,
                              $this->updatingTableName);
        $sqlCondition2 = $this->getCondition();

        $this->condition = '(' . $sqlCondition1 . ' AND ' . $sqlCondition2 . ')';
    }

    public function processOrCondition(Condition $condition1, Condition $condition2)
    {
        $this->writeCondition($condition1,
                              $this->rootTableName,
                              $this->updatingTableNumber,
                              $this->updatingTableName);
        $sqlCondition1 = $this->getCondition();

        $this->writeCondition($condition2,
                              $this->rootTableName,
                              $this->updatingTableNumber,
                              $this->updatingTableName);
        $sqlCondition2 = $this->getCondition();

        $this->condition = '(' . $sqlCondition1 . ' OR ' . $sqlCondition2 . ')';
    }

    public function processStorableConditionReferenceAsCondition($name, $datatypeName, Condition $condition)
    {
        $join = $this->storage->getJoin($this->currentTable, $name);

        if ($join == $this->updatingTableNumber)
        {
            $this->updatingTableCondition = $condition;
        }
        else
        {
            if ($join == -1)
            {
                $join = $this->storage->createJoin($this->currentTable, $name, $datatypeName, 'id');
            }

            $subWriter = new UpdateConditionWriter($this->storage, $join);
            $subWriter->writeSimpleCondition($condition);

            if (!$this->phase2)
            {
                $this->joining .= ' JOIN `' . $this->storage->tableNamify($datatypeName) .
                                                                    '` AS `t' . $join . '`';
                $this->joining .= ' ON `t' . $this->currentTable . '`.`' .
                                                    $this->storage->fieldNamify($name) . '`';
                $this->joining .= ' = `t' . $join . '`.`id`';

                $this->joining .= $subWriter->getJoining();
                $this->writeBracketOrAnd();
                $this->condition .= $subWriter->getCondition();
            }
            else
            {
                $this->writeBracketOrAnd();
                $this->condition .= ' `' . $this->tableName . '`.`' .
                                            $this->storage->fieldNamify($name) . '`';
                $this->condition .= ' IN (SELECT `t' . $join . '`.`id`';
                $this->condition .= ' FROM `' . $this->storage->tableNamify($datatypeName) .
                                                            '` AS `t' . $join . '`';

                $this->condition .= $subWriter->getJoining();
                $this->condition .= ' WHERE ' . $subWriter->getCondition();
                $this->condition .= ')';
            }
        }
    }

    public function processStorableConditionReferenceAsComparison($name, $comparison)
    {
        $this->writeBracketOrAnd();

        $field = $this->getTableName();
        $field .=  '.`' . $this->storage->fieldNamify($name) . '` ';
        $fragmentWriter = new ReferenceFragmentWriter($field);

        $this->condition .= $fragmentWriter->writeFragment($comparison);
    }

    public function processStorableConditionId(EqualityComparison $comparison)
    {
        $this->writeBracketOrAnd();

        $field = '`t' . $this->currentTable . '`.`id`';
        $fragmentWriter = new IntFragmentWriter($this->storage, $field);

        $this->condition .= $fragmentWriter->writeFragment($comparison);
    }

    public function processStorableConditionText($name, $value)
    {
        $this->writeBracketOrAnd();

        $field = $this->getTableName();
        $field .=  '.`' . $this->storage->fieldNamify($name) . '` ';
        $fragmentWriter = new TextFragmentWriter($this->storage, $field);

        $this->condition .= $fragmentWriter->writeFragment($value);
    }

    public function processStorableConditionInt($name, $value)
    {
        $this->writeBracketOrAnd();

        $field = $this->getTableName();
        $field .=  '.`' . $this->storage->fieldNamify($name) . '` ';
        $fragmentWriter = new IntFragmentWriter($this->storage, $field);

        $this->condition .= $fragmentWriter->writeFragment($value);
    }

    public function processStorableConditionFloat($name, $value)
    {
        $this->writeBracketOrAnd();

        $field = $this->getTableName();
        $field .=  '.`' . $this->storage->fieldNamify($name) . '` ';
        $fragmentWriter = new FloatFragmentWriter($this->storage, $field);

        $this->condition .= $fragmentWriter->writeFragment($value);
    }

    public function processStorableConditionDateTime($name, $value)
    {
        $this->writeBracketOrAnd();

        $field = $this->getTableName();
        $field .=  '.`' . $this->storage->fieldNamify($name) . '` ';
        $fragmentWriter = new DateTimeFragmentWriter($this->storage, $field);

        $this->condition .= $fragmentWriter->writeFragment($value);
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

    private function getTableName()
    {
        if ($this->phase2)
        {
            return '`' . $this->tableName . '`';
        }
        else
        {
            return '`t' . $this->currentTable . '`';
        }
    }
}

?>
