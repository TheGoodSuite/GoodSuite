<?php

namespace Good\Manners;

use Ds\Set;

use Good\Service\CollectionBehaviorModifier;

class StorableCollectionModifier implements CollectionBehaviorModifier
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
            throw new \Exception("Unable to convert unresolved collection to array");
        }
    }

    public function count()
    {
        if (!$this->resolved)
        {
            throw new \Exception("Unable to convert unresolved collection to array");
        }
    }

    public function clean()
    {
        $this->addedItems->clear();
        $this->removedItems->clear();
        $this->cleared = false;
    }

    public function markUnresolved()
    {
        $this->resolved = false;
    }
}

?>
