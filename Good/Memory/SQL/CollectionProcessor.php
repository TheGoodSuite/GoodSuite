<?php

namespace Good\Memory\SQL;

use Good\Manners\Storable;
use Good\Manners\StorableVisitor;
use Good\Memory\Database\Database;
use Good\Memory\SQLStorage;
use Good\Memory\StorableCollectionEntry;
use Good\Service\Type;
use Good\Rolemodel\TypeVisitor;
use Good\Rolemodel\Schema\Type\ReferenceType;
use Good\Rolemodel\Schema\Type\TextType;
use Good\Rolemodel\Schema\Type\IntType;
use Good\Rolemodel\Schema\Type\FloatType;
use Good\Rolemodel\Schema\Type\DatetimeType;
use Good\Rolemodel\Schema\Type\CollectionType;

class CollectionProcessor implements StorableVisitor, TypeVisitor
{
    private $db;
    private $storage;

    private $currentOwner;

    private $storables;
    private $deletedCollectionValues;
    private $deletedSQLValues;

    public function __construct(Database $db, SQLStorage $storage)
    {
        $this->db = $db;
        $this->storage = $storage;
    }

    public function processCollections(Storable $value)
    {
        $this->storables = [];

        $this->currentOwner = $value;

        $value->acceptStorableVisitor($this);

        return $this->storables;
    }

    public function visitCollectionProperty($name, $value, $modifier)
    {
        if ($modifier->wasCleared() || $this->currentOwner->isDeleted())
        {
            $this->clearCollectionQuery($this->currentOwner, $name);
        }

        if (!$this->currentOwner->isDeleted())
        {
            foreach ($modifier->getAddedItems() as $item)
            {
                $entry = $this->createStorableCollectionEntry($value->getCollectedType(), $this->currentOwner, $name, $item);

                $this->storables[] = $entry;
            }

            $deletedCollectionValues = [];

            $this->deletedCollectionValues = $modifier->getRemovedItems();

            if (count($this->deletedCollectionValues) > 0)
            {
                $this->deletedSQLValues = [];

                $value->getCollectedType()->acceptTypeVisitor($this);

                $sql = $this->getDeleteFromCollectionStatement($this->currentOwner, $name);
                $sql .= '  AND `value` IN (' . \implode(', ', $this->deletedSQLValues) . ')';

                $this->db->query($sql);
            }
        }

        $modifier->clean();
    }

    private function clearCollectionQuery(Storable $owner, $collectionFieldName)
    {
        $sql = $this->getDeleteFromCollectionStatement($owner, $collectionFieldName);

        $this->db->query($sql);
    }

    private function getDeleteFromCollectionStatement(Storable $owner, $collectionFieldName)
    {
        $sql = 'DELETE FROM `' . $this->storage->tableNamify($owner->getType())
            . '_' . $this->storage->tableNamify($collectionFieldName) . '`';
        $sql .= ' WHERE `owner` = ' . \intval($owner->id);

        return $sql;
    }

    private function createStorableCollectionEntry(Type $collectedType, Storable $owner, $collectionFieldName, $value)
    {
        $typeName = $owner->getType() . '_' . $collectionFieldName;

        $collectionEntry = new StorableCollectionEntry($collectedType, $typeName);
        $collectionEntry->setValue($value);
        $collectionEntry->setOwner($owner);

        return $collectionEntry;
    }

    public function visitReferenceProperty($name, $datatypeName, $dirty,
                                                        Storable $value = null)
    {
    }

    public function visitTextProperty($name, $dirty, $value) {}
    public function visitIntProperty($name, $dirty, $value) {}
    public function visitFloatProperty($name, $dirty, $value) {}
    public function visitDatetimeProperty($name, $dirty, $value) {}

    public function visitReferenceType(ReferenceType $type)
    {
        foreach ($this->deletedCollectionValues as $value)
        {
            $this->deletedSQLValues[] = \intval($value->getId());
        }
    }

    public function visitTextType(TextType $type)
    {
        foreach ($this->deletedCollectionValues as $value)
        {
            $this->deletedSQLValues[] = $this->storage->parseText($value->getId());
        }
    }

    public function visitIntType(IntType $type)
    {
        foreach ($this->deletedCollectionValues as $value)
        {
            $this->deletedSQLValues[] = $this->storage->parseInt($value);
        }
    }

    public function visitFloatType(FloatType $type)
    {
        foreach ($this->deletedCollectionValues as $value)
        {
            $this->deletedSQLValues[] = $this->storage->parseFloat($value);
        }
    }

    public function visitDateTimeType(DatetimeType $type)
    {
        foreach ($this->deletedCollectionValues as $value)
        {
            $this->deletedSQLValues[] = $this->storage->parseDatetime($value);
        }
    }

    public function visitCollectionType(CollectionType $type)
    {
        // Collections of collections are not supported
    }
}

?>
