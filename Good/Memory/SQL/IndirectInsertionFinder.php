<?php

namespace Good\Memory\SQL;

use Good\Manners\Storable;
use Good\Manners\StorableVisitor;
use Good\Memory\SQLStorage;
use Good\Rolemodel\TypeVisitor;
use Good\Rolemodel\Schema\Type\ReferenceType;
use Good\Rolemodel\Schema\Type\TextType;
use Good\Rolemodel\Schema\Type\IntType;
use Good\Rolemodel\Schema\Type\FloatType;
use Good\Rolemodel\Schema\Type\DatetimeType;
use Good\Rolemodel\Schema\Type\CollectionType;

class IndirectInsertionFinder implements StorableVisitor, TypeVisitor
{
    private $storage;
    private $currentCollectionItems;

    public function __construct(SQLStorage $storage)
    {
        $this->storage = $storage;
    }

    public function findIndirectInsertions(Storable $value)
    {
        $value->acceptStorableVisitor($this);
    }

    public function visitReferenceProperty($name, $datatypeName, $dirty,
                                                        Storable $value = null)
    {
        $this->handleReference($value);
    }

    public function visitTextProperty($name, $dirty, $value) {}
    public function visitIntProperty($name, $dirty, $value) {}
    public function visitFloatProperty($name, $dirty, $value) {}
    public function visitDatetimeProperty($name, $dirty, $value) {}

    public function visitCollectionProperty($name, $value, $modifier)
    {
        if ($modifier->isResolved())
        {
            $this->currentCollectionItems = $value;
        }
        else
        {
            $this->currentCollectionItems = $modifier->getAddedItems();
        }

        $value->getCollectedType()->acceptTypeVisitor($this);
    }

    public function visitReferenceType(ReferenceType $type)
    {
        $items = $this->currentCollectionItems;

        foreach ($items as $item)
        {
            $this->handleReference($item);
        }
    }

    private function handleReference($reference)
    {
        if ($reference === null)
        {
            return;
        }

        if ($reference->isNew() && !$this->storage->hasDirtyStorable($reference))
        {
            $this->storage->dirtyStorable($reference);

            $reference->acceptStorableVisitor($this);
        }
    }

    public function visitTextType(TextType $type) {}
    public function visitIntType(IntType $type) {}
    public function visitFloatType(FloatType $type) {}
    public function visitDateTimeType(DatetimeType $type) {}
    public function visitCollectionType(CollectionType $type) {}

}

?>
