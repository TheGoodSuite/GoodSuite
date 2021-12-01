<?php

namespace Good\Memory\SQL;

use Good\Memory\SQLStorage;
use Good\Memory\CollectionEntryCondition;
use Good\Memory\SQL\ConditionWriter\DateTimeFragmentWriter;
use Good\Memory\SQL\ConditionWriter\FloatFragmentWriter;
use Good\Memory\SQL\ConditionWriter\IntFragmentWriter;
use Good\Memory\SQL\ConditionWriter\ReferenceFragmentWriter;
use Good\Memory\SQL\ConditionWriter\TextFragmentWriter;
use Good\Manners\Storable;
use Good\Manners\Condition;
use Good\Manners\Condition\ComplexCondition;
use Good\Manners\Processors\ComplexConditionProcessor;
use Good\Manners\Processors\ConditionProcessor;
use Good\Manners\Processors\CollectionConditionProcessor;
use Good\Manners\CollectionCondition;
use Good\Rolemodel\TypeVisitor;
use Good\Rolemodel\Schema\Type\ReferenceType;
use Good\Rolemodel\Schema\Type\TextType;
use Good\Rolemodel\Schema\Type\IntType;
use Good\Rolemodel\Schema\Type\FloatType;
use Good\Rolemodel\Schema\Type\DateTimeType;
use Good\Rolemodel\Schema\Type\CollectionType;
use Good\Service\Type;

class ConditionWriter implements ComplexConditionProcessor, ConditionProcessor, CollectionConditionProcessor, TypeVisitor
{
    private $storage;
    private $sqlCondition;
    private $having;
    private $first;

    private $currentTable;
    private $currentTableName;

    private $fieldName;
    private $condition;

    public function __construct(SQLStorage $storage, $currentTable, $currentTableName)
    {
        $this->storage = $storage;
        $this->currentTable = $currentTable;
        $this->currentTableName = $currentTableName;
    }

    public function getCondition()
    {
        return $this->sqlCondition;
    }

    public function getHaving()
    {
        return $this->having;
    }

    public function writeCondition(Condition $condition)
    {
        $this->first = true;
        $this->sqlCondition = '';
        $this->having = null;

        $condition->processCondition($this);

        if ($this->first)
        {
            $this->sqlCondition = '1 = 1';
        }
    }

    public function writeCollectionCondition(CollectionType $type, $collectionName, CollectionCondition $condition)
    {
        $this->first = true;
        $this->sqlCondition = '';
        $this->having = null;
        $this->type = $type;
        $this->collectionName = $collectionName;

        $condition->processCollectionCondition($this);

        if ($this->first)
        {
            $this->sqlCondition = '1 = 1';
        }
    }

    public function processAndCondition(Condition $condition1, Condition $condition2)
    {
        $this->writeCondition($condition1);
        $sqlCondition1 = $this->getCondition();
        $having1 = $this->getHaving();

        $this->writeCondition($condition2);
        $sqlCondition2 = $this->getCondition();

        $this->sqlCondition = '(' . $sqlCondition1 . ' AND ' . $sqlCondition2 . ')';
        $this->appendHaving($having1);

    }

    public function processOrCondition(Condition $condition1, Condition $condition2)
    {
        $this->writeCondition($condition1);
        $sqlCondition1 = $this->getCondition();
        $having1 = $this->getHaving();

        $this->writeCondition($condition2);
        $sqlCondition2 = $this->getCondition();

        $this->sqlCondition = '(' . $sqlCondition1 . ' OR ' . $sqlCondition2 . ')';
        $this->appendHaving($having1);
    }

    public function processEqualToCondition($value)
    {
        if (!($value instanceof Storable) && $value->id != null)
        {
            throw new \Exception("EqualTo can only be used as a query condition when targeting an existing Storable");
        }

        $this->writeBracketOrAnd();

        $field = '`t' . $this->currentTable . '`.`id`';
        $fragmentWriter = new IntFragmentWriter($this->storage);

        $this->sqlCondition .= $fragmentWriter->writeIdEquals($value->id, $field);
    }

    public function processNotEqualToCondition($value)
    {
        if (!($value instanceof Storable) && $value->id != null)
        {
            throw new \Exception("NotEqualTo can only be used as a query condition when targeting an existing Storable");
        }

        $this->writeBracketOrAnd();

        $field = '`t' . $this->currentTable . '`.`id`';
        $fragmentWriter = new IntFragmentWriter($this->storage);

        $this->sqlCondition .= $fragmentWriter->writeIdNotEqual($value->id, $field);
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

    public function processId(Condition $condition)
    {
        $this->writeBracketOrAnd();

        $field = '`t' . $this->currentTable . '`.`id`';
        $fragmentWriter = new IntFragmentWriter($this->storage);

        $this->sqlCondition .= $fragmentWriter->writeFragment($condition, $field);
    }

    public function processMember(Type $type, $name, Condition $condition)
    {
        $this->fieldName = $name;
        $this->condition = $condition;

        $type->acceptTypeVisitor($this);
    }

    public function visitReferenceType(ReferenceType $type)
    {
        $complexConditionDiscoverer = new ComplexConditionDiscoverer();
        $complexCondition = $complexConditionDiscoverer->discoverComplexCondition($this->condition);

        if ($complexCondition != null)
        {
            $this->writeBracketOrAnd();

            $join = $this->storage->getJoin($this->currentTable, $this->fieldName);

            if ($join == -1)
            {
                $join = $this->storage->createJoin($this->currentTable, $this->fieldName, $type->getReferencedType(), 'id');
            }

            $subWriter = new ConditionWriter($this->storage, $join, $type->getReferencedType());
            $subWriter->writeCondition($complexCondition);

            $this->sqlCondition .= $subWriter->getCondition();
            $this->appendHaving($subWriter->getHaving());
        }
        else
        {
            $this->writeBracketOrAnd();

            $field = '`t' . $this->currentTable . '`.`' . $this->storage->fieldNamify($this->fieldName) . '`';
            $fragmentWriter = new ReferenceFragmentWriter();

            $this->sqlCondition .= $fragmentWriter->writeFragment($this->condition, $field);
        }
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

        $this->sqlCondition .= $fragmentWriter->writeFragment($this->condition, $field);
    }

    private $collectionName;
    private $type;

    public function processCollectionMember(CollectionType $type, $name, CollectionCondition $condition)
    {
        $this->collectionName = $name;
        $this->type = $type;

        $condition->processCollectionCondition($this);
    }

    public function processHasA(Condition $condition)
    {
        $this->writeBracketOrAnd();

        $table = $this->currentTableName . '_' . $this->collectionName;
        $join = $this->storage->createJoin($this->currentTable, 'id', $table, 'owner', null, false);

        $subWriter = new ConditionWriter($this->storage, $join, $this->currentTableName);
        $subWriter->writeCondition(new CollectionEntryCondition($this->type->getCollectedType(), $condition));

        $this->sqlCondition .= $subWriter->getCondition();
        $this->appendHaving($subWriter->getHaving());
    }

    public function processHasOnly(Condition $condition)
    {
        $this->writeBracketOrAnd();

        $table = $this->currentTableName . '_' . $this->collectionName;
        $join = $this->storage->createJoin($this->currentTable, 'id', $table, 'owner', null, false);

        $subWriter = new ConditionWriter($this->storage, $join, $this->currentTableName);
        $subWriter->writeCondition(new CollectionEntryCondition($this->type->getCollectedType(), $condition));

        $secondJoin = $this->storage->createJoin($this->currentTable, 'id', $table, 'owner', null, false);

        $this->appendHaving("COUNT(DISTINCT `t" . $join . "`.`value`) = COUNT(DISTINCT `t" . $secondJoin . "`.`value`)");

        $this->sqlCondition .= '(' . $subWriter->getCondition() . ' OR `t' . $join . '`.`owner` IS NULL)';
        $this->appendHaving($subWriter->getHaving());
    }

    public function processAndCollection(CollectionCondition $condition1, CollectionCondition $condition2)
    {
        $subWriter = new ConditionWriter($this->storage, $this->currentTable, $this->currentTableName);

        $subWriter->writeCollectionCondition($this->type, $this->collectionName, $condition1);
        $sqlCondition1 = $subWriter->getCondition();
        $sqlHaving1 = $subWriter->getHaving();

        $subWriter->writeCollectionCondition($this->type, $this->collectionName, $condition2);
        $sqlCondition2 = $subWriter->getCondition();
        $sqlHaving2 = $subWriter->getHaving();

        $this->writeBracketOrAnd();
        $this->sqlCondition = '(' . $sqlCondition1 . ' AND ' . $sqlCondition2 . ')';
        $this->appendHaving($sqlHaving1);
        $this->appendHaving($sqlHaving2);
    }

    public function processOrCollection(CollectionCondition $condition1, CollectionCondition $condition2)
    {
        $subWriter = new ConditionWriter($this->storage, $this->currentTable, $this->currentTableName);

        $subWriter->writeCollectionCondition($this->type, $this->collectionName, $condition1);
        $sqlCondition1 = $subWriter->getCondition();
        $sqlHaving1 = $subWriter->getHaving();

        $subWriter->writeCollectionCondition($this->type, $this->collectionName, $condition2);
        $sqlCondition2 = $subWriter->getCondition();
        $sqlHaving2 = $subWriter->getHaving();

        $this->writeBracketOrAnd();
        $this->sqlCondition = '(' . $sqlCondition1 . ' OR ' . $sqlCondition2 . ')';
        $this->appendHaving($sqlHaving1);
        $this->appendHaving($sqlHaving2);
    }

    private function writeBracketOrAnd()
    {
        if ($this->first)
        {
            // removed brackets change name of function?
            //$this->sqlCondition = '(';
            $this->first = false;
        }
        else
        {
            $this->sqlCondition .= ' AND ';
        }
    }

    private function appendHaving($having)
    {
        if ($having != null)
        {
            if ($this->having == null)
            {
                $this->having = $having;
            }
            else
            {
                $this->having .= ' AND ';
                $this->having .= $having;
            }
        }
    }
}

?>
