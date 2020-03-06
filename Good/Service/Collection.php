<?php

namespace Good\Service;

use Ds\Set;

class Collection implements \IteratorAggregate
{
    private $items;

    private $collectedType;

    private $behaviorModifiers = [];

    public function __construct(Type $collectedType)
    {
        $this->collectedType = $collectedType;

        $this->items = new Set();
    }

    public function registerBehaviorModifier(CollectionBehaviorModifier $modifier)
    {
        $this->behaviorModifiers[] = $modifier;
    }

    public function add($value)
    {
        foreach($this->behaviorModifiers as $modifier)
        {
            $modifier->add($value);
        }

        $this->collectedType->checkValue($value);

        $this->items->add($value);
    }

    public function remove($value)
    {
        foreach($this->behaviorModifiers as $modifier)
        {
            $modifier->remove($value);
        }

        $this->items->remove($value);
    }

    public function clear()
    {
        foreach($this->behaviorModifiers as $modifier)
        {
            $modifier->clear();
        }

        foreach ($this->items as $item)
        {
            $this->items->remove($item);
        }
    }

    public function toArray()
    {
        foreach($this->behaviorModifiers as $modifier)
        {
            $modifier->toArray();
        }

        return $this->items->toArray();
    }

    public function getIterator()
    {
        foreach($this->behaviorModifiers as $modifier)
        {
            $modifier->getIterator();
        }

        return new \IteratorIterator($this->items);
    }

    public function getCollectedType()
    {
        return $this->collectedType;
    }

    public function count()
    {
        foreach($this->behaviorModifiers as $modifier)
        {
            $modifier->count();
        }

        return $this->items->count();
    }
}

?>
