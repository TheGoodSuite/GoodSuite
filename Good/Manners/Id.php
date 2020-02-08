<?php

namespace Good\Manners;

use Good\Manners\Storable;
use Good\Manners\Storage;
use Good\Manners\Resolver;
use Good\Manners\Condition\EqualTo;

class Id implements Storable
{
    private $base;
    private $id;

    private $storage = null;
    private $deleted = false;

    public function __construct(Storable $base, Storage $storage, $id)
    {
        $this->base = $base;
        $this->storage = $storage;
        $this->id = $id;
    }

    public function setStorage(Storage $storage)
    {
        // Does this function even make sense?
        $this->storage = $storage;
    }

    public function delete()
    {
        $this->deleted = true;
        $this->storage->dirtyStorable($this);
    }

    public function isDeleted()
    {
        return $this->deleted;
    }

    public function isDirty()
    {
        // Only way to make an id dirty is to delete it
        return $this->deleted;
    }

    public function setNew($value)
    {
    }

    public function isNew()
    {
        return false;
    }

    public function setValidationToken(ValidationToken $token)
    {
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        // Does this function even make sense?
        $this->id = id;
    }

    public function hasValidId()
    {
        return true;
    }

    public function clean()
    {
        // ?
    }

    public function getType()
    {
        return $this->base->getType();
    }

    public function acceptStorableVisitor(StorableVisitor $visitor)
    {
        // This doesn't need to do anything, because we don't care about the fields of an id
    }

    public function get(Resolver $resolver = null)
    {
        $collection = $this->storage->getCollection(new EqualTo($this), $resolver);

        $first = $collection->getNext();

        if ($first === null)
        {
            throw new \Exception("Id not found in storage");
        }

        return $first;
    }

    public function __get($property)
    {
        if ($property === "id")
        {
            return $this->getId();
        }
        else
        {
            throw new \Exception("Unable to get any property other than 'id' from an unfetched object.");
        }
    }
}

?>
