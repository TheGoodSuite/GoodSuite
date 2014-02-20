<?php

namespace Good\Manners;

interface StorableCollection extends \IteratorAggregate
{
    function getNext();
}

?>