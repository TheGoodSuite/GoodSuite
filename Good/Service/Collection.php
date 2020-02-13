<?php

namespace Good\Service;

use Ds\Set;

class Collection implements \IteratorAggregate
{
    private $items;

    private $owner;
    private $ownerProperty;

    public function __construct($owner, $ownerProperty)
    {
        $this->owner = $owner;
        $this->ownerProperty = $ownerProperty;

        $this->items = new Set();
    }

    public function add($value)
    {
        $this->owner->checkCollectionItem($this->ownerProperty, $value);

        $this->items->add($value);
    }

    public function remove($value)
    {
        $this->items.remove($value);
    }

    public function getIterator()
    {
        return new \IteratorIterator($this->items);
    }
}

?>
