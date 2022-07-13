<?php

namespace Good\Memory;

use Ds\Set;

use Good\Manners\Storage;
use Good\Manners\Storable;
use Good\Manners\Condition;
use Good\Manners\Condition\EqualTo;
use Good\Manners\Resolver;
use Good\Manners\ResolvableCollection;
use Good\Manners\Page;
use Good\Memory\SQL\IndirectInsertionFinder;
use Good\Memory\SQL\CollectionProcessor;

class SQLStorage extends Storage
{
    private $db;

    private $joins = array();
    private $joinsReverse = array();
    private $numberOfJoins = 0;

    private $managedStorables;

    private $postponed = array();

    public function __construct($db)
    {
        parent::__construct();

        $this->db = $db;

        $this->managedStorables = new Set();
    }

    public function insert(Storable $storable)
    {
        $this->managedStorables->add($storable);
    }

    public function modifyAny(Condition $condition, Storable $modifications)
    {
        $this->flush();

        $this->joins = array(0 => array());
        $this->numberOfJoins = 0;

        $updater = new SQL\AdvancedUpdater($this, $this->db, 0);

        $updater->update($modifications->getType(), $condition, $modifications);
    }

    public function fetchAll($conditionOrResolver, $resolverOrPage = null, Page $page = null)
    {
        if ($resolverOrPage == null || ($resolverOrPage instanceof Page))
        {
            if ($conditionOrResolver instanceof Condition)
            {
                $condition = $conditionOrResolver;

                if ($condition->getTargetedReferenceType() === null || $condition->getTargetedReferenceType() == "*")
                {
                    throw new \Exception("Condition for fetchAll must target an unambigious Storable type");
                }

                $resolver = $condition->getTargetedReferenceType()::resolver();
            }
            else if ($conditionOrResolver instanceof Resolver)
            {
                $resolver = $conditionOrResolver->getRoot();
                $condition = $resolver->getType()::condition();
            }
            else
            {
                throw new \InvalidArgumentException("When called with one argument, "
                    . "the argument to getCollection should be a Condition or a "
                    . "Resolver, but it was neither");
            }
        }
        else
        {
            if ($conditionOrResolver instanceof Condition)
            {
                $resolver = $resolverOrPage->getRoot();
                $condition = $conditionOrResolver;
            }
            else
            {
                throw new \InvalidArgumentException("When called with two arguments, "
                    . "the first argument to getCollection should be a Condition.");
            }
        }

        if ($page === null && ($resolverOrPage instanceof Page))
        {
            $page = $resolverOrPage;
        }
        else if ($page !== null && ($resolverOrPage instanceof Page))
        {
            throw new \InvalidArgumentException("Only one Page object may be passed to fetchAll.");
        }

        if ($resolverOrPage !== null
            && !($resolverOrPage instanceof Resolver)
            && !($resolverOrPage instanceof Page))
        {
            throw new \InvalidArgumentException("Argument 2 for fetchAll must be either a Page or a Resolver.");
        }

        if ($condition->getTargetedReferenceType() === null || $condition->getTargetedReferenceType() == "*")
        {
            throw new \Exception("Condition for fetchAll must target an unambigious Storable type");
        }

        $resultset = $this->select($condition, $resolver, $page);

        return new FetchedStorables($this, $resultset, $this->joins, $resolver->getType());
    }

    private function select($condition, $resolver, ?Page $page, $withId = true)
    {
        $this->joins = array(0 => array());
        $this->numberOfJoins = 0;

        $selecter = new SQL\Selecter($this, $this->db, 0);

        if ($withId)
        {
            return $selecter->select($resolver->getType(), $condition, $resolver, $page);
        }
        else
        {
            return $selecter->selectWithoutId($resolver->getType(), $condition, $resolver, $page);
        }
    }

    public function resolve(Storable $storable, Resolver $resolver = null)
    {
        if ($resolver == null)
        {
            $storable::resolver();
        }

        if ($storable->id === null)
        {
            throw new \Exception("Can only resolve a storable that has an id");
        }

        $condition = new EqualTo($storable);

        $results = $this->fetchAll($condition, $resolver);
        $result = $results->resolveNext($storable);

        if ($result == null) {
            throw new \Exception("Storable to resolve was not found in storage");
        }

        return $result;
    }

    public function resolveCollection(ResolvableCollection $collection, Resolver $resolver = null)
    {
        if ($collection->getStorable()->id === null)
        {
            throw new \Exception("Can only resolve a collection on a storable that has an id");
        }

        $collectedReferenceType = $collection->getCollectedType()->getReferencedTypeIfAny();

        if ($resolver === null && $collectedReferenceType !== null)
        {
            $resolver = $collectedReferenceType::resolver();
        }

        $storable = $collection->getStorable();

        $condition = new CollectionOwnerCondition($storable);
        $resolver = new CollectionEntryResolver($storable->getType(), $collection->getFieldName(), $resolver);

        $resultset = $this->select($condition, $resolver, null, false);

        $data = [];
        $rows = [];
        $lastValue = null;

        while ($row = $resultset->fetch())
        {
            if ($lastValue === null || $lastValue === $row['t0_value'])
            {
                $lastValue = $row['t0_value'];
            }
            else
            {
                $allData = $this->preparseSQLData($rows);

                $data[] = $this->getStorableOrValue('value', $rows[0]['t0_value'], $this->joins, 0, $allData, 0);
                $rows = [];
            }

            $rows[] = $row;
        }

        $allData = $this->preparseSQLData($rows);

        $data[] = $this->getStorableOrValue('value', $rows[0]['t0_value'], $this->joins, 0, $allData, 0);

        $collection->resolveWithData($data);

        return $collection;
    }

    public function isManagedStorable(Storable $storable)
    {
        return $this->managedStorables->contains($storable);
    }

    private $flushing =  false;
    private $reflush = false;

    public function flush()
    {
        if ($this->flushing)
        {
            $this->reflush = true;
            return;
        }

        $this->findIndirectInsertions();

        $collectionEntryStorables = $this->processCollections();

        // Add to end: nothing can depend on a collection entry, so that's safe
        $storables = $this->managedStorables->copy();
        $storables->add(...$collectionEntryStorables);

        $this->flushing = true;

        $deleted = array();
        $modified = array();
        $new = array();

        foreach ($storables as $dirty)
        {
            if ($dirty->isDeleted() && !$dirty->isNew())
            {
                $deleted[] = $dirty;
            }
            else if ($dirty->isNew() && !$dirty->isDeleted())
            {
                $new[] = $dirty;
            }
            else if (!$dirty->isNew() && $dirty->isDirty())
            {
                $modified[] = $dirty;
            }
        }

        if (count($new) > 0)
        {
            $inserter = new SQL\Inserter($this, $this->db);

            foreach ($new as $storable)
            {
                // We check again if it is new, as it might already be inserted when resolving dependencies
                // of another insert, in which case it is not new anymore.
                if ($storable->isNew())
                {
                    $inserter->insert($storable->getType(), $storable);
                }
            }

            $allPostponed = $inserter->getPostponed();

            foreach ($allPostponed as $postponed)
            {
                $postponed->doNow();
            }

            if (count($allPostponed) > 0)
            {
                $this->flush();
            }
        }

        if (count($modified) > 0)
        {
            $updater = new SQL\SimpleUpdater($this, $this->db);

            foreach ($modified as $storable)
            {
                $updater->update($storable->getType(), $storable);
                $storable->clean();
            }
        }

        if (count($deleted) > 0)
        {
            foreach ($deleted as $storable)
            {
                $sql  = 'DELETE FROM `' . $this->tableNamify($storable->getType()) . '`';
                $sql .= " WHERE `id` = " . intval($storable->getId());

                $this->db->query($sql);
            }
        }

        $this->flushing = false;

        if ($this->reflush)
        {
            $this->reflush = false;
            $this->flush();
        }
    }

    private function findIndirectInsertions()
    {
        $indirectInsertionFinder = new IndirectInsertionFinder($this);

        foreach ($this->managedStorables->copy() as $dirty)
        {
            $indirectInsertionFinder->findIndirectInsertions($dirty);
        }
    }

    private function processCollections()
    {
        $collectionEntryStorables = [];
        $collectionProcessor = new CollectionProcessor($this->db, $this);

        foreach ($this->managedStorables as $dirty)
        {
            array_push(
                $collectionEntryStorables,
                ...$collectionProcessor->processCollections($dirty));
        }

        return $collectionEntryStorables;
    }

    public function tableNamify($value)
    {
        return \strtolower($value);
    }

    public function fieldNamify($value)
    {
        return \strtolower($value);
    }

    public function parseInt($value)
    {
        return \intval($value);
    }

    public function parseFloat($value)
    {
        return \floatval($value);
    }

    public function parseDatetime($value)
    {
        // shouldn't be necessary when we do stricter type checking,
        // but let's just stick with it for now.
        if (!($value instanceof \DateTimeImmutable))
        {
            // TODO: turn this into real error reporting
            throw new \Exception("Non-DateTimeImmutable given for a DateTimeImmutable field.");
        }

        return "'" . $value->setTimeZone(new \DateTimeZone("UTC"))->format('Y-m-d H:i:s') . "'";
    }

    public function parseBoolean($value)
    {
        return $value ? 'TRUE' : 'FALSE';
    }

    public function parseText($value)
    {
        return "'" . $this->db->escapeText($value) . "'";
    }

    public function getJoin($table, $field)
    {
        if (\array_key_exists($this->fieldNamify($field), $this->joins[$table]))
        {
            return $this->joins[$table][$this->fieldNamify($field)]->tableNumberDestination;
        }
        else
        {
            return -1;
        }
    }

    public function getReverseJoin($tableNumber)
    {
        if (\array_key_exists($tableNumber, $this->joinsReverse))
        {
            return $this->joinsReverse[$tableNumber];
        }
        else
        {
            return null;
        }
    }

    public function getJoins()
    {
        return $this->joins;
    }

    public function createJoin($tableNumberOrigin, $fieldNameOrigin, $tableNameDestination, $fieldNameDestination, $selectedFieldName = null, $reusable = true)
    {
        if ($selectedFieldName == null)
        {
            $selectedFieldName = $fieldNameOrigin;
        }

        // we start off with increment because joins index is numberOfJoins + 1 (index 0 is for base table)
        $this->numberOfJoins++;

        $join = new SQL\Join($tableNumberOrigin,
                             $fieldNameOrigin,
                             $tableNameDestination,
                             $this->numberOfJoins,
                             $fieldNameDestination,
                             $this->fieldNamify($selectedFieldName));

        if ($reusable)
        {
            // We fieldnamify the key here because it's the most flexible
            // It means you can get the join if you have the non fieldnamified type
            // (you can just call fieldnamify on it) as well as when you do have the
            // fieldnamified type.
            $this->joins[$tableNumberOrigin][$this->fieldNamify($selectedFieldName)] = $join;
        }
        else
        {
            $this->joins[-1 * $tableNumberOrigin - 1][] = $join;
        }

        $this->joins[$this->numberOfJoins] = array();
        $this->joinsReverse[$this->numberOfJoins] = $join;

        return $this->numberOfJoins;
    }

    public function createStorable(array $allData, $joins, $type)
    {
        $storable = $this->createEmptyStorable($type);

        return $this->writeStorable($allData, $joins, $storable, 0, false, 0);
    }

    public function resolveStorable(Storable $storable, array $allData, $joins)
    {
        return $this->writeStorable($allData, $joins, $storable, 0, false, 0);
    }

    public function writeStorable(array $allData, $joins, Storable $storable, $tableNumber, $dataPreparsed, $offset)
    {
        if (!$dataPreparsed)
        {
            $allData = $this->preparseSQLData($allData);
        }

        $data = $allData[$offset];
        $table = 't' . $tableNumber;

        if (array_key_exists($data[$table]["id"],
        // todo: allow for proper checking again, after solving the problems that brought with it.
        // note: the following line has changed since it was commenting out.
        //          it'll probably need a second array_key_exists when it is uncommented again.
        //       (on ($type, $this->created))
        //                                                    $this->created[$type]))' . "\n";
        // for now, we just concoct something that will always evaluate to false
                                                            array()))
        {
            return $this->created[$storable->getType()][$data[$table]["id"]];
        }

        $storableData = array();

        foreach ($data[$table] as $field => $value)
        {
            $valueOrStorable = $this->getStorableOrValue(
                $field,
                $value,
                $joins,
                $tableNumber,
                $allData,
                0);

            if ($valueOrStorable !== null || array_key_exists($field, $joins[$tableNumber]))
            {
                $storableData[$field] = $valueOrStorable;
            }
        }

        foreach ($data[$table] as $fieldName => $fieldValue)
        {
            if (\substr($fieldName, -8) === ' thisrow')
            {
                $field = \str_replace(' thisrow', '', $fieldName);

                if ($storableData[$field] === null)
                {
                    $storableData[$field] = [];
                }
                else
                {
                    $storableData[$field] = [$storableData[$field]];
                }
            }
        }

        $iterator = (new \ArrayObject(array_keys($allData)))->getIterator();
        $key = $iterator->current();
        $row = $allData[$key];
        do {
            $collectionField = $this->findFirstCollectionField($row[$table]);

            if ($collectionField === null)
            {
                $iterator->next();
                if ($iterator->valid())
                {
                    $key = $iterator->current();
                    $row = $allData[$key];
                }
            }
            else
            {
                $lastValue = null;
                while ($iterator->valid() && $row[$table][$collectionField . ' thisrow'])
                {
                    $currentValue = $row[$table][$this->tableNamify($collectionField)];

                    if ($lastValue === null || $lastValue !== $currentValue)
                    {
                        if (!is_array($storableData[$collectionField]))
                        {
                            $storableData[$collectionField] = [];
                        }

                        $storableData[$collectionField][] = $this->getStorableOrValue(
                            $this->tableNamify($collectionField),
                            $currentValue,
                            $joins,
                            $tableNumber,
                            $allData,
                            $key);

                        $lastValue = $currentValue;
                    }

                    $iterator->next();
                    if ($iterator->valid())
                    {
                        $key = $iterator->current();
                        $row = $allData[$key];
                    }
                }
            }
        } while ($iterator->valid() && $row[$table]["id"] == $data[$table]["id"]);

        $denamifier = new FieldDenamifier($this);
        $storableData = $denamifier->denamifyFields($storableData, $storable);

        $storable->markCollectionsUnresolved();

        $storable->setStorage($this);
        $storable->setFromArray($storableData);
        $storable->setId(strval($data[$table]["id"]));

        $storable->setNew(false);
        $storable->clean();

        $this->created[$storable->getType()][$data[$table]["id"]] = $storable;
        $this->managedStorables->add($storable);

        return $storable;
    }

    private function findFirstCollectionField($row)
    {
        foreach ($row as $fieldName => $fieldValue)
        {
            if (\substr($fieldName, -8) === ' thisrow' && $fieldValue)
            {
                return \str_replace(' thisrow', '', $fieldName);
            }
        }

        return null;
    }

    private function preparseSQLData($allData)
    {
        $parsed = array();

        foreach ($allData as $index => $dataRow)
        {
            $parsed[$index] = [];

            foreach ($dataRow as $field => $value)
            {
                $parsed[$index][strstr($field, '_', true)][substr($field, strpos($field, '_') + 1)] = $value;
            }
        }

        return $parsed;
    }

    private function getStorableOrValue($field, $value, $joins, $tableNumber, $allData, $dataRow)
    {
        if ($value === null)
        {
            return null;
        }

        if (array_key_exists($field, $joins[$tableNumber])
            && $joins[$tableNumber][$field]->fieldNameDestination == 'id'
            && array_key_exists('t' . $joins[$tableNumber][$field]->tableNumberDestination, $allData[$dataRow]))
        {
            // this is a resolved reference
            $type = $joins[$tableNumber][$field]->tableNameDestination;
            $storable = $this->createEmptyStorable($type);

            return $this->writeStorable(
                $allData,
                $joins,
                $storable,
                $joins[$tableNumber][$field]->tableNumberDestination,
                true,
                $dataRow);
        }
        else if (array_key_exists($field, $joins[$tableNumber])
            && $joins[$tableNumber][$field]->fieldNameDestination == 'owner')
        {
            // collection
            $destinationTable = $joins[$tableNumber][$field]->tableNumberDestination;

            return $this->getStorableOrValue(
                'value',
                $value,
                $joins,
                $destinationTable,
                $allData,
                $dataRow);
        }
        else
        {
            // non-reference
            return $value;
        }
    }
}

?>
