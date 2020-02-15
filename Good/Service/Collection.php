<?php

namespace Good\Service;

use Ds\Set;

class Collection implements \IteratorAggregate
{
    private $items;

    private $collectedType;

    public function __construct(Type $collectedType)
    {
        $this->collectedType = $collectedType;

        $this->items = new Set();
    }

    public function add($value)
    {
        $this->collectedType->checkValue($value);

        $this->items->add($value);
    }

    public function remove($value)
    {
        $this->items.remove($value);
    }

    public function clear()
    {
        foreach ($this->items as $item)
        {
            $this->remove($item);
        }
    }

    public function toArray()
    {
        return $this->items->toArray();
    }

    public function getIterator()
    {
        return new \IteratorIterator($this->items);
    }
}

?>
