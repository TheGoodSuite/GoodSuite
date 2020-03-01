<?php

namespace Good\Service;

interface CollectionBehaviorModifier
{
    public function add($value);
    public function remove($value);
    public function clear();
    public function toArray();
    public function getIterator();
    public function count();
}

?>
