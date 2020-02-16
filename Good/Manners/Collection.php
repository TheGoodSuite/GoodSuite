<?php

class Collection implements \IteratorAggregate
{
    public function addItem(Storable $storable) {}
    public function removeItem(Storable $storable) {}
    public function clear();
    public function isResolved() {};
    public function fetch() {};

    public function hasAny(Condition $condition): Condition\Collection\HasAny {}
    public function hasOnly(Condition $condition): Condition\Collection\HasOnly {}
}

?>
