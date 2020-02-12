<?php

namespace Good\Service;

class Collection
{
    private $owner;
    private $ownerProperty;

    public function __construct($owner, $ownerProperty)
    {
        $this->owner = $owner;
        $this->ownerProperty = $ownerProperty;
    }

    public function add($value)
    {
        $this->owner->checkCollectionItem($ownerProperty, $value);
    }
}

?>
