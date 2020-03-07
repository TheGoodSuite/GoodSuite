<?php

namespace Good\Manners;

interface FetchedStorables extends \IteratorAggregate
{
    function getNext();
}

?>
