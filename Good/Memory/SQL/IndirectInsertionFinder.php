<?php

namespace Good\Memory\SQL;

use Good\Manners\Storable;
use Good\Manners\StorableVisitor;

class IndirectInsertionFinder implements StorableVisitor
{
    private $indirectInsertions;

    public function __construct()
    {
    }

    public function findIndirectInsertions(Storable $value)
    {
        $this->indirectInsertions = [];

        $value->acceptStorableVisitor($this);

        return $this->indirectInsertions;
    }

    public function visitReferenceProperty($name, $datatypeName, $dirty,
                                                        Storable $value = null)
    {
        if ($value === null)
        {
            return;
        }

        if ($value->isNew())
        {
            $this->indirectInsertions[] = $value;
        }
    }

    public function visitTextProperty($name, $dirty, $value) {}
    public function visitIntProperty($name, $dirty, $value) {}
    public function visitFloatProperty($name, $dirty, $value) {}
    public function visitDatetimeProperty($name, $dirty, $value) {}

    public function visitCollectionProperty($name, $value, $modifier) {}
}

?>
