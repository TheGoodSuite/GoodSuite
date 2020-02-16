<?php

namespace Good\Memory\SQL;

use Good\Memory\Database as Database;

use Good\Memory\SQLStorage;
use Good\Manners\Condition;
use Good\Manners\Resolver;
use Good\Manners\ResolverVisitor;

class Selecter implements ResolverVisitor
{
    private $db;
    private $storage;

    private $subquery;

    private $sql;
    private $currentTable;
    private $currentTableName;

    private $order = array();

    public function __construct(SQLStorage $storage, Database\Database $db, $currentTable)
    {
        $this->db = $db;
        $this->storage = $storage;
        $this->currentTable = $currentTable;
    }

    public function select($datatypeName, Condition $condition, Resolver $resolver)
    {
        $this->currentTableName = $this->storage->tableNamify($datatypeName);

        $this->sql = "SELECT `t0`.`id` AS `t0_id`";

        $resolver->acceptResolverVisitor($this);

        $this->sql .= $this->writeQueryWithoutSelect($datatypeName, $condition);

        $this->db->query($this->sql);

        return $this->db->getResult();
    }

    public function writeQueryWithoutSelect($datatypeName,
                                            Condition $condition)
    {
        $sql  = " FROM `" . $this->storage->tableNamify($datatypeName) . "` AS t0";

        $conditionWriter = new ConditionWriter($this->storage, 0);
        $conditionWriter->writeCondition($condition);

        foreach ($this->storage->getJoins() as $somejoins)
        {
            foreach ($somejoins as $join)
            {
                $sql .= ' LEFT JOIN `' . $this->storage->tableNamify($join->tableNameDestination) .
                                                    '` AS `t' . $join->tableNumberDestination . '`';
                $sql .= ' ON `t' . $join->tableNumberOrigin . '`.`' .
                                            $this->storage->fieldNamify($join->fieldNameOrigin) . '`';
                $sql .= ' = `t' . $join->tableNumberDestination . '`.`' . $join->fieldNameDestination . '`';
            }
        }

        $sql .= ' WHERE ' . $conditionWriter->getCondition();

        // Code below can't simply be replaced by a foreach or implode,
        // because that will happen in the order the entries are created
        // and we want to use the numerical indices as order.
        // One could use "ksort", but I believe this is more efficient
        // in most cases.
        for ($i = 0; $i < \count($this->order); $i++)
        {
            if ($i == 0)
            {
                $sql .= ' ORDER BY ' . $this->order[$i];
            }
            else
            {
                $sql .= ', ' . $this->order[$i];
            }
        }

        return $sql;
    }

    public function resolverVisitResolvedReferenceProperty($name, $datatypeName, Resolver $resolver)
    {
        $this->writeSelectJoinedFields($datatypeName, $resolver, $name, 'id', false, $name);
    }

    public function resolverVisitResolvedCollectionProperty($name)
    {
        $this->writeSelectJoinedFields($this->currentTableName . '_' . $name, null, 'id', 'owner', true, $name);
    }

    public function writeSelectJoinedFields($joinTable, ?Resolver $resolver,
        $currentTableJoinField, $otherTableJoinField, $isCollectionJoin, $joinTriggerField)
    {
        if (!$isCollectionJoin)
        {
            $this->sql .= ', ';
            $this->sql .= '`t' . $this->currentTable . '`.`' . $this->storage->fieldNamify($currentTableJoinField) . '`';
            $this->sql .= ' AS `t' . $this->currentTable . '_' . $this->storage->fieldNamify($currentTableJoinField) . '`';
        }

        $join = $this->storage->createJoin($this->currentTable,
                                           $currentTableJoinField,
                                           $joinTable,
                                           $otherTableJoinField,
                                           !$isCollectionJoin);

        if ($isCollectionJoin)
        {
            $this->sql .= ', ';
            $this->sql .= '`t' . $join . '`.`value`';
            $this->sql .= ' AS `t' . $this->currentTable . '_' . $this->storage->fieldNamify($joinTriggerField) . '`';
        }
        else
        {
            $this->sql .= ', ';
            $this->sql .= '`t' . $join . '`.`id` AS `t' . $join . '_id`';
        }

        $currentTable = $this->currentTable;
        $currentTableName = $this->currentTableName;
        $this->currentTable = $join;
        $this->currentTableName = $joinTable;

        if ($resolver != null)
        {
            $resolver->acceptResolverVisitor($this);
        }


        $this->currentTable = $currentTable;
        $this->currentTableName = $currentTableName;
    }

    public function resolverVisitUnresolvedReferenceProperty($name)
    {
    }

    public function resolverVisitUnresolvedCollectionProperty($name)
    {
    }

    public function resolverVisitScalarProperty($name)
    {
        $this->sql .= ', ';

        $this->sql .= '`t' . $this->currentTable . '`.`' . $this->storage->fieldNamify($name) . '`';
        $this->sql .= ' AS `t' . $this->currentTable . '_' . $this->storage->fieldNamify($name) . '`';
    }

    public function resolverVisitOrderAsc($number, $name)
    {
        $this->order[$number] = '`t' . $this->currentTable . '_' .
                        $this->storage->fieldnamify($name) . '` ASC';
    }

    public function resolverVisitOrderDesc($number, $name)
    {
        $this->order[$number] = '`t' . $this->currentTable . '_' .
                        $this->storage->fieldnamify($name) . '` DESC';
    }
}

?>
