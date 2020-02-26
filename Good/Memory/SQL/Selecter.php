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

    private $orderRootLayer;
    private $orderLayer;

    private $currentPropertyIsCollection;

    public function __construct(SQLStorage $storage, Database\Database $db, $currentTable)
    {
        $this->db = $db;
        $this->storage = $storage;
        $this->currentTable = $currentTable;
    }

    public function select($datatypeName, Condition $condition, Resolver $resolver)
    {
        $this->orderRootLayer = new OrderLayer(0);
        $this->orderLayer = $this->orderRootLayer;
        $this->currentPropertyIsCollection = false;

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

        $order = $this->gatherOrderClauses($this->orderRootLayer);

        if (\count($order) > 0)
        {
            $sql .= ' ORDER BY ';
            $sql .= \implode(', ', $order);
        }

        return $sql;
    }

    private function gatherOrderClauses($orderLayer)
    {
        $orderClauses = $orderLayer->orderClauses;
        \ksort($orderClauses);

        if (count($orderLayer->childLayers) > 0)
        {
            $orderClauses[] = '`t' . $orderLayer->rootTableNumber . '`.`id` ASC';
        }

        foreach ($orderLayer->childLayers as $childLayer)
        {
            $childOrderClauses = $this->gatherOrderClauses($childLayer);

            $orderClauses = array_merge($orderClauses, $childOrderClauses);
        }

        return $orderClauses;
    }

    public function resolverVisitResolvedReferenceProperty($name, $datatypeName, Resolver $resolver)
    {
        $this->currentPropertyIsCollection = false;

        $this->writeSelectJoinedFields($this->currentTable, $datatypeName, $resolver, $name, 'id', null, true);
    }

    public function resolverVisitResolvedScalarCollectionProperty($name)
    {
        $this->currentPropertyIsCollection = true;

        $this->writeSelectJoinedFields($this->currentTable, $this->currentTableName . '_' . $name, null, 'id', 'owner', $name, false);
    }

    public function resolverVisitResolvedReferenceCollectionProperty($name, $typeName, Resolver $resolver)
    {
        $this->currentPropertyIsCollection = true;

        $table = $this->writeSelectJoinedFields($this->currentTable, $this->currentTableName . '_' . $name, null, 'id', 'owner', $name, false);
        $this->writeSelectJoinedFields($table, $typeName, $resolver, 'value', 'id', null, false);
    }

    public function writeSelectJoinedFields($leftTableNumber, $joinTable, ?Resolver $resolver,
        $currentTableJoinField, $otherTableJoinField, $collectionField, $selectJoinField)
    {
        if ($selectJoinField)
        {
            $this->sql .= ', ';
            $this->sql .= '`t' . $leftTableNumber . '`.`' . $this->storage->fieldNamify($currentTableJoinField) . '`';
            $this->sql .= ' AS `t' . $leftTableNumber . '_' . $this->storage->fieldNamify($currentTableJoinField) . '`';
        }

        $join = $this->storage->createJoin($leftTableNumber,
                                           $currentTableJoinField,
                                           $joinTable,
                                           $otherTableJoinField,
                                           $collectionField);

        if ($collectionField === null)
        {
            $this->sql .= ', ';
            $this->sql .= '`t' . $join . '`.`id` AS `t' . $join . '_id`';
        }
        else
        {
            $this->sql .= ', ';
            $this->sql .= '`t' . $join . '`.`value`';
            $this->sql .= ' AS `t' . $leftTableNumber . '_' . $this->storage->fieldNamify($collectionField) . '`';
        }

        if ($resolver != null)
        {
            $currentTable = $this->currentTable;
            $currentTableName = $this->currentTableName;
            $this->currentTable = $join;
            $this->currentTableName = $joinTable;

            if ($collectionField !== null)
            {
                $orderLayer = $this->orderLayer;
                $this->orderLayer = new OrderLayer($join);
                $orderLayer->childLayers[] = $this->orderLayer;
            }

            $resolver->acceptResolverVisitor($this);

            if ($collectionField !== null)
            {
                $this->orderLayer = $orderLayer;
            }

            $this->currentTable = $currentTable;
            $this->currentTableName = $currentTableName;
        }

        return $join;
    }

    public function resolverVisitUnresolvedReferenceProperty($name)
    {
        $this->currentPropertyIsCollection = false;
    }

    public function resolverVisitUnresolvedCollectionProperty($name)
    {
        $this->currentPropertyIsCollection = true;
    }

    public function resolverVisitScalarProperty($name)
    {
        $this->currentPropertyIsCollection = false;

        $this->sql .= ', ';

        $this->sql .= '`t' . $this->currentTable . '`.`' . $this->storage->fieldNamify($name) . '`';
        $this->sql .= ' AS `t' . $this->currentTable . '_' . $this->storage->fieldNamify($name) . '`';
    }

    public function resolverVisitOrderAsc($number, $name)
    {
        $this->writeOrderTerm($number, $name, 'ASC');
    }

    public function resolverVisitOrderDesc($number, $name)
    {
        $this->writeOrderTerm($number, $name, 'DESC');
    }

    private function writeOrderTerm($number, $name, $direction)
    {
        $clause = '`t' . $this->currentTable . '_' .
            $this->storage->fieldnamify($name) . '` ' . $direction;

        if ($this->currentPropertyIsCollection)
        {
            // We don't know the table number here, but we do know it's not relevant...
            $childOrderLayer = new OrderLayer(null, [$number => $clause]);
            $this->orderLayer->childLayers[] = $childOrderLayer;
        }
        else
        {
            $this->orderLayer->orderClauses[$number] = $clause;
        }
    }
}

?>
