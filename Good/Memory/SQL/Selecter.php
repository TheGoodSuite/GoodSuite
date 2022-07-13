<?php

namespace Good\Memory\SQL;

use Good\Memory\Database as Database;

use Ds\Set;
use Good\Memory\SQLStorage;
use Good\Manners\Condition;
use Good\Manners\Resolver;
use Good\Manners\ResolverVisitor;
use Good\Manners\Page;

class Selecter implements ResolverVisitor
{
    private $db;
    private $storage;

    private $subquery;

    private $currentTable;
    private $currentTableName;

    private $orderLayer;

    private $currentPropertyIsCollection;

    private $columns;

    private $includeJoinsForPagination;
    private $paginationJoins;

    public function __construct(SQLStorage $storage, Database\Database $db, $currentTable)
    {
        $this->db = $db;
        $this->storage = $storage;
        $this->currentTable = $currentTable;
    }

    public function select($datatypeName, Condition $condition, Resolver $resolver, ?Page $page)
    {
        $columns = [];
        $columns[] = new SelectColumn("t0", "id", "t0_id");
        $this->orderRootLayer = new OrderLayer(0);

        return $this->selectWithBaseColumns($columns, $datatypeName, $condition, $resolver, $page);
    }

    public function selectWithoutId($datatypeName, Condition $condition, Resolver $resolver)
    {
        $columns = [];
        $this->orderRootLayer = new OrderLayer(1);

        return $this->selectWithBaseColumns($columns, $datatypeName, $condition, $resolver, null);
    }

    private function selectWithBaseColumns($columns, $datatypeName, Condition $condition, Resolver $resolver, ?Page $page)
    {
        $this->columns = $columns;

        $this->orderLayer = $this->orderRootLayer;
        $this->currentPropertyIsCollection = false;
        $this->includeJoinsForPagination = true;
        $this->paginationJoins = [];

        $this->currentTableName = $this->storage->tableNamify($datatypeName);

        $resolver->acceptResolverVisitor($this);
        $order = $this->gatherOrderClauses($this->orderRootLayer);

        $sql = $this->writeQueryForColumns($datatypeName, $condition, $this->columns, $order, $page);

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

    public function writeQueryForColumns($datatypeName, Condition $condition, $columns, $order, ?Page $page)
    {
        $sql = "SELECT DISTINCT ";

        $tableName = $this->storage->tableNamify($datatypeName);
        $fromSql = " FROM `" . $tableName . "` AS t0";

        $conditionWriter = new ConditionWriter($this->storage, 0, $datatypeName);
        $conditionWriter->writeCondition($condition);

        $previousCollectionTableNumbers = [];
        $extraSelects = '';

        foreach ($this->storage->getJoins() as $somejoins)
        {
            foreach ($somejoins as $join)
            {
                $table = '`' . $this->storage->tableNamify($join->tableNameDestination) . '`';
                $on = '`t' . $join->tableNumberOrigin . '`.`' . $this->storage->fieldNamify($join->fieldNameOrigin) . '`';
                $on .= ' = `t' . $join->tableNumberDestination . '`.`' . $join->fieldNameDestination . '`';

                if ($join->fieldNameDestination === 'owner')
                {
                    $table = '(SELECT * from ' . $table . ' UNION SELECT NULL, NULL)';

                    $on = '(' . $on . ' OR `t' . $join->tableNumberDestination  . '`.`owner` IS NULL)';

                    $ancestorJoin = $join;
                    $ancestorCollections = [];
                    while ($ancestorJoin !== null)
                    {
                        $ancestorCollections[$ancestorJoin->tableNumberDestination] = true;

                        $ancestorJoin = $this->storage->getReverseJoin($ancestorJoin->tableNumberOrigin);
                    }

                    foreach ($previousCollectionTableNumbers as $tableNumber)
                    {
                        if (!\array_key_exists($tableNumber, $ancestorCollections))
                        {
                            $on .= ' AND `t' . $tableNumber . '`.`owner` IS NULL';
                        }
                    }

                    $extraSelects .= ', `t' . $join->tableNumberDestination . '`.`owner` IS NOT NULL AS `t' . $join->tableNumberOrigin . '_' . $join->selectedFieldName  . ' thisrow`';

                    $previousCollectionTableNumbers[] = $join->tableNumberDestination;
                }

                $fromSql .= ' LEFT JOIN ' . $table . ' AS `t' . $join->tableNumberDestination . '`';
                $fromSql .= ' ON ' . $on;
            }
        }

        $columnsSQL = array_map([$this, 'columnToSelectClause'], $columns);
        $sql .= \implode(', ', $columnsSQL);
        $sql .= $extraSelects;
        $sql .= $fromSql;

        if ($page !== null)
        {
            $regex = '/^`t([0-9]+)_/';

            $orderOnRootTable = $this->orderRootLayer->orderClauses;
            \ksort($orderOnRootTable);

            $orderOnRootTable = array_map(function($orderBy) use ($regex)
            {
                return \preg_replace($regex, '`t$1`.`', $orderBy);
            }, $orderOnRootTable);

            $over = '';
            if (\count($orderOnRootTable) > 0)
            {
                $over .= 'ORDER BY ';
                $over .= \implode(', ', $orderOnRootTable);
            }

            $sql .= ' LEFT JOIN (SELECT `t0`.`id`, ROW_NUMBER() OVER (' . $over . ') as `row` FROM `' . $tableName;
            $sql .= '` as `t0` ';

            foreach ($this->paginationJoins as $join)
            {
                $sql .= ' LEFT JOIN `' . $this->storage->tableNamify($join->tableNameDestination) .
                                                    '` AS `t' . $join->tableNumberDestination . '`';
                $sql .= ' ON `t' . $join->tableNumberOrigin . '`.`' .
                                            $this->storage->fieldNamify($join->fieldNameOrigin) . '`';
                $sql .= ' = `t' . $join->tableNumberDestination . '`.`' . $join->fieldNameDestination . '`';
            }

            $sql .= ') as `pagination`';
            $sql .= ' ON `t0`.`id` = `pagination`.`id`';
        }

        $sql .= ' WHERE ' . $conditionWriter->getCondition();

        if ($page !== null)
        {
            if ($page->getStartAt() === null)
            {
                $endAt = $page->getSize();
            }
            else
            {
                $sql .= ' AND `pagination`.`row` >= ' . ($page->getStartAt() + 1);
                $endAt = $page->getSize() + $page->getStartAt();
            }

            $sql .= ' AND `pagination`.`row` <= ' . $endAt;
        }

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

        $orderLayer = new OrderLayer(null);
        $orderLayer->orderClauses[-1] = '`t' . $this->currentTable . '_' . $this->storage->tableNamify($name) . ' thisrow` ASC';

        $this->orderLayer->childLayers[] = $orderLayer;

        $this->writeSelectJoinedFields($this->currentTable, $this->currentTableName . '_' . $name, null, 'id', 'owner', $name, false);
    }

    public function resolverVisitResolvedReferenceCollectionProperty($name, $typeName, Resolver $resolver)
    {
        $this->currentPropertyIsCollection = true;

        $table = $this->writeSelectJoinedFields($this->currentTable, $this->currentTableName . '_' . $name, null, 'id', 'owner', $name, false);

        if ($resolver !== null)
        {
            $orderLayer = $this->orderLayer;
            $this->orderLayer = new OrderLayer($table);
            $orderLayer->childLayers[] = $this->orderLayer;
            $includeJoinsForPagination = $this->includeJoinsForPagination;
            $this->includeJoinsForPagination = false;

            $this->orderLayer->orderClauses[-1] = '`t' . $this->currentTable . '_' . $this->storage->tableNamify($name) . ' thisrow` ASC';
        }

        $table = $this->writeSelectJoinedFields($table, $typeName, $resolver, 'value', 'id', null, false);

        if ($resolver !== null)
        {
            $this->orderLayer->rootTableNumber = $table;
            $this->orderLayer = $orderLayer;
            $this->includeJoinsForPagination = $includeJoinsForPagination;
        }
    }

    private function writeSelectJoinedFields($leftTableNumber, $joinTable, ?Resolver $resolver,
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

            if ($this->includeJoinsForPagination)
            {
                $paginationJoin = new Join($leftTableNumber,
                    $currentTableJoinField,
                    $joinTable,
                    $join,
                    $otherTableJoinField,
                    null);

                $this->paginationJoins[] = $paginationJoin;
            }
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

            $resolver->acceptResolverVisitor($this);

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
