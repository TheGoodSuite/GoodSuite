<?php

namespace Good\Memory;

use Ds\Set;

use Good\Manners\Storage;
use Good\Manners\Storable;
use Good\Manners\Condition;
use Good\Manners\Condition\EqualTo;
use Good\Manners\Resolver;
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

    public function getCollection($conditionOrResolver, Resolver $resolver = null)
    {
        if ($resolver == null)
        {
            if ($conditionOrResolver instanceof Condition)
            {
                $condition = $conditionOrResolver;
                $resolver = $condition->getTargetType()::resolver();
            }
            else if ($conditionOrResolver instanceof Resolver)
            {
                $resolver = $conditionOrResolver->getRoot();
                $type = $resolver->getType();
                $condition = new EqualTo(new $type);
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
                $resolver = $resolver->getRoot();
                $condition = $conditionOrResolver;
            }
            else
            {
                throw new \InvalidArgumentException("When called with two arguments, "
                    . "the first argument to getCollection should be a Condition.");
            }
        }

        $this->joins = array(0 => array());
        $this->numberOfJoins = 0;

        $selecter = new SQL\Selecter($this, $this->db, 0);

        // I can't really do this on the next line, where I create the collection, since
        // I also use something that as a side effect of this function call.
        // (I don't know if php guarantees the order in which arguments are calculated,
        //  but even if it does, it makes for confusing code to rely on it)
        $resultset = $selecter->select($resolver->getType(), $condition, $resolver);

        return new StorableCollection($this, $resultset, $this->joins, $resolver->getType());
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

    public function createJoin($tableNumberOrigin, $fieldNameOrigin, $tableNameDestination, $fieldNameDestination, $selectedFieldName = null)
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
                             $fieldNameDestination);

        // We fieldnamify the key here because it's the most flexible
        // It means you can get the join if you have the non fieldnamified type
        // (you can just call fieldnamify on it) as well as when you do have the
        // fieldnamified type.
        $this->joins[$tableNumberOrigin][$this->fieldNamify($selectedFieldName)] = $join;

        $this->joins[$this->numberOfJoins] = array();
        $this->joinsReverse[$this->numberOfJoins] = $join;

        return $this->numberOfJoins;
    }

    public function createStorable(array $allData, $joins, $type, $tableNumber = 0, $dataPreparsed = false, $offset = 0)
    {
        if (!$dataPreparsed)
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

            $allData = $parsed;
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
            return $this->created[$type][$data[$table]["id"]];
        }

        $storableData = array();

        foreach ($data[$table] as $field => $value)
        {
            $storableData[$field] = $this->getStorableOrValue(
                $field,
                $value,
                $joins,
                $tableNumber,
                $allData,
                0);
        }

        $ret = $this->storableFactory->createStorable($type);

        $denamifier = new FieldDenamifier($this);
        $storableData = $denamifier->denamifyFields($storableData, $ret);

        foreach ($allData as $key => $row)
        {
            if ($key != 0 && $row[$table]['id'] === $data[$table]['id'])
            {
                foreach ($storableData as $fieldName => $value)
                {
                    if (is_array($value))
                    {
                        $storableData[$fieldName][] = $this->getStorableOrValue(
                            $this->tableNamify($fieldName),
                            $row[$table][$this->tableNamify($fieldName)],
                            $joins,
                            $tableNumber,
                            $allData,
                            $key);
                    }
                }
            }
        }

        $ret->markCollectionsUnresolved();

        $ret->setFromArray($storableData);
        $ret->setId(strval($data[$table]["id"]));

        $ret->setNew(false);
        $ret->clean();
        $ret->setStorage($this);

        $this->created[$type][$data[$table]["id"]] = $ret;
        $this->managedStorables->add($ret);

        return $ret;
    }

    private function getStorableOrValue($field, $value, $joins, $tableNumber, $allData, $dataRow)
    {
        if ($value === null)
        {
            return null;
        }

        if (array_key_exists($field, $joins[$tableNumber])
            && $joins[$tableNumber][$field]->fieldNameDestination == 'id')
        {
            // this is a resolved reference
            return $this->createStorable($allData,
                                         $joins,
                                         $joins[$tableNumber][$field]->tableNameDestination,
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
