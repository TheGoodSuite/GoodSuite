<?php

namespace Good\Manners;

use Ds\Set;

use Good\Service\CollectionBehaviorModifier;

class CollectionModifierStorable implements CollectionBehaviorModifier
{
    private $cleared;
    private $resolved;

    private $removedItems;
    private $addedItems;

    public function __construct()
    {
        $this->cleared = false;
        $this->resolved = true;

        $this->removedItems = new Set();
        $this->addedItems = new Set();
    }

    public function add($value)
    {
        $this->removedItems->remove($value);
        $this->addedItems->add($value);
    }

    public function remove($value)
    {
        $this->addedItems->remove($value);
        $this->removedItems->add($value);
    }

    public function clear()
    {
        $this->cleared = true;
        $this->resolved = true;
        $this->addedItems->clear();
        $this->removedItems->clear();
    }

    public function toArray()
    {
        if (!$this->resolved)
        {
            throw new \Exception("Unable to convert unresolved collection to array");
        }
    }

    public function getIterator()
    {
        if (!$this->resolved)
        {
            throw new \Exception("Unable to iterate unresolved collection");
        }
    }

    public function count()
    {
        if (!$this->resolved)
        {
            throw new \Exception("Unable to count unresolved collection");
        }
    }

    public function clean()
    {
        $this->addedItems->clear();
        $this->removedItems->clear();
        $this->cleared = false;
    }

    public function markResolved()
    {
        $this->resolved = true;
    }

    public function markUnresolved()
    {
        $this->resolved = false;
    }


    public function isDirty()
    {
        return $this->cleared
            || count($this->addedItems) != 0
            || count($this->removedItems) != 0;
    }

    public function isResolved()
    {
        return $this->resolved;
    }

    public function getAddedItems()
    {
        return $this->addedItems;
    }

    public function getRemovedItems()
    {
        return $this->removedItems;
    }

    public function wasCleared()
    {
        return $this->cleared;
    }
}

?>
