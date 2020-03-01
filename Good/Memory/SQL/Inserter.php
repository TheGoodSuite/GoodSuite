<?php

namespace Good\Memory\SQL;

use Good\Memory\Database as Database;
use Good\Memory\StorableCollectionEntry;

use Good\Memory\SQLStorage;
use Good\Memory\SQLPostponedForeignKey;
use Good\Manners\Storable;
use Good\Manners\StorableVisitor;

class Inserter implements StorableVisitor
{
    private $db;
    private $storage;

    private $sql;
    private $values;
    private $first;

    private $inserting;
    private $postponed;

    private $collectionEntries;

    public function __construct(SQLStorage $storage, Database\Database $db)
    {
        $this->db = $db;
        $this->storage = $storage;
        $this->postponed = array();
    }


    public function insert($datatypeName, Storable $value)
    {
        $this->collectionEntries = [];

        $this->sql = "INSERT INTO `" . $this->storage->tableNamify($datatypeName) . '` (';
        $this->values = 'VALUES (';
        $this->first = true;

        $this->inserting = $value;

        $value->setNew(false);
        $value->setStorage($this->storage);

        $value->acceptStorableVisitor($this);

        $this->sql .= ') ';
        $this->sql .= $this->values . ')';

        $this->db->query($this->sql);
        // next line: not necessary for `CollectionEntry`s
        $value->setId(\strval($this->db->getLastInsertedId()));
        $value->clean();

        $collectionEntryInserter = new Inserter($this->storage, $this->db);

        foreach ($this->collectionEntries as $collectionEntry)
        {
            $collectionEntry->setOwner($value);

            $collectionEntryInserter->insert($datatypeName . '_' . $collectionEntry->getCollectionFieldName(), $collectionEntry);
            $this->postponed = \array_merge($this->postponed, $collectionEntryInserter->getPostponed());
        }

        $this->collectionEntries = [];
    }

    private function comma()
    {
        if ($this->first)
        {
            $this->first = false;
        }
        else
        {
            $this->sql .= ', ';
            $this->values .= ', ';
        }
    }

    public function getPostponed()
    {
        return $this->postponed;
    }

    public function visitReferenceProperty($name, $datatypeName, $dirty,
                                                        Storable $value = null)
    {
        // If not dirty, do not include field and use default value
        if ($dirty)
        {
            $this->comma();

            $this->sql .= '`' . $this->storage->fieldNamify($name) . '`';

            if ($value === null)
            {
                $this->values .= 'NULL';
            }
            else
            {
                if ($value->isNew())
                {
                    $inserter = new Inserter($this->storage, $this->db);
                    $inserter->insert($datatypeName, $value);
                    $this->postponed = \array_merge($this->postponed, $inserter->getPostponed());
                }

                if (!$value->isNew() && !$value->hasValidId())
                // $value is actually new, but not marked as such to prevent infinite recursion
                {
                    $this->postponed[] = new SQLPostponedForeignKey($this->inserting,
                                                                    $name,
                                                                    $value);
                    $this->values .= 'NULL';
                }
                else
                {
                    $this->values .= \intval($value->getId());
                }
            }
        }
    }

    public function visitTextProperty($name, $dirty, $value)
    {
        // If not dirty, do not include field and use default value
        if ($dirty)
        {
            $this->comma();

            $this->sql .= '`' . $this->storage->fieldNamify($name) . '`';

            if ($value === null)
            {
                $this->values .= 'NULL';
            }
            else
            {
                $this->values .= $this->storage->parseText($value);
            }
        }
    }

    public function visitIntProperty($name, $dirty, $value)
    {
        // If not dirty, do not include field and use default value
        if ($dirty)
        {
            $this->comma();

            $this->sql .= '`' . $this->storage->fieldNamify($name) . '`';

            if ($value === null)
            {
                $this->values .= 'NULL';
            }
            else
            {
                $this->values .= $this->storage->parseInt($value);
            }
        }
    }

    public function visitFloatProperty($name, $dirty, $value)
    {
        // If not dirty, do not include field and use default value
        if ($dirty)
        {
            $this->comma();

            $this->sql .= '`' . $this->storage->fieldNamify($name) . '`';

            if ($value === null)
            {
                $this->values .= 'NULL';
            }
            else
            {
                $this->values .= $this->storage->parseFloat($value);
            }
        }
    }

    public function visitDatetimeProperty($name, $dirty, $value)
    {
        // If not dirty, do not include field and use default value
        if ($dirty)
        {
            $this->comma();

            $this->sql .= '`' . $this->storage->fieldNamify($name) . '`';

            if ($value === null)
            {
                $this->values .= 'NULL';
            }
            else
            {
                $this->values .= $this->storage->parseDatetime($value);
            }
        }
    }

    public function visitCollectionProperty($name, $collection, $modifier)
    {
        foreach ($collection as $value)
        {
            $collectionEntry = new StorableCollectionEntry($collection->getCollectedType());
            $collectionEntry->setValue($value);
            $collectionEntry->setCollectionFieldName($name);

            $this->collectionEntries[] = $collectionEntry;
        }
    }
}

?>
