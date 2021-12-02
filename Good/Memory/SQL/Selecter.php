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

    private $currentTable;
    private $currentTableName;

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
        $orderRootLayer = new OrderLayer(0);
        $this->orderLayer = $orderRootLayer;
        $this->currentPropertyIsCollection = false;


        $this->columns = [];
        $this->columns[] = new SelectColumn("t0", "id", "t0_id");

        $this->currentTableName = $this->storage->tableNamify($datatypeName);

        $resolver->acceptResolverVisitor($this);
        $order = $this->gatherOrderClauses($orderRootLayer);

        $sql = $this->writeQueryForColumns($datatypeName, $condition, $this->columns, $order);

        $this->db->query($sql);

        return $this->db->getResult();
    }

    private function columnToSelectClause($column)
    {
        $clause  = '`' . $column->table . '`';
        $clause .= '.`' . $column->column . '`';
        $clause .= ' AS `' . $column->as . '`';

        return $clause;
    }

    public function writeQueryForColumns($datatypeName, Condition $condition, $columns, $order)
    {
        $sql = "SELECT DISTINCT ";

        $columnsSQL = array_map([$this, 'columnToSelectClause'], $columns);
        $sql .= \implode(', ', $columnsSQL);

        $sql .= " FROM `" . $this->storage->tableNamify($datatypeName) . "` AS t0";

        $conditionWriter = new ConditionWriter($this->storage, 0, $datatypeName);
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

        if ($conditionWriter->getHaving() != null)
        {
            $groupBySQL = array_map([$this, 'getEscapedAs'], $columns);

            $sql .= ' GROUP BY ' . \implode(', ', $groupBySQL);

            $sql .= ' HAVING ' . $conditionWriter->getHaving();
        }

        if (\count($order) > 0)
        {
            $sql .= ' ORDER BY ';
            $sql .= \implode(', ', $order);
        }

        return $sql;
    }

    private function getEscapedAs($column)
    {
        return '`' . $column->as . '`';
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
            $table = 't' . $leftTableNumber;
            $column = $this->storage->fieldNamify($currentTableJoinField);
            $as = $table . '_' . $column;

            $this->columns[] = new SelectColumn($table, $column, $as);
        }

        $join = $this->storage->createJoin($leftTableNumber,
                                           $currentTableJoinField,
                                           $joinTable,
                                           $otherTableJoinField,
                                           $collectionField);

        if ($collectionField === null)
        {
            $this->columns[] = new SelectColumn('t' . $join, 'id', 't' . $join . '_id');
        }
        else
        {
            $table = 't' . $join;
            $column = 'value';
            $as = 't' . $leftTableNumber . '_' . $this->storage->fieldNamify($collectionField);

            $this->columns[] = new SelectColumn($table, $column, $as);
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

        $table = 't' . $this->currentTable;
        $column = $this->storage->fieldNamify($name);
        $as = $table . '_' . $column;

        $this->columns[] = new SelectColumn($table, $column, $as);
    }

    public function resolverVisitUnresolvedCollectionProperty($name)
    {
        $this->currentPropertyIsCollection = true;
    }

    public function resolverVisitScalarProperty($name)
    {
        $this->currentPropertyIsCollection = false;

        $table = 't' . $this->currentTable;
        $column = $this->storage->fieldNamify($name);
        $as = $table . '_' . $column;

        $this->columns[] = new SelectColumn($table, $column, $as);
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
