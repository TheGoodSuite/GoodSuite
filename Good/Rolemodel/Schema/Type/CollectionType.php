<?php

namespace Good\Rolemodel\Schema\Type;

use Good\Rolemodel\Schema\Type;
use Good\Rolemodel\TypeVisitor;

class CollectionType implements Type
{
    private $collectedType;

    public function __construct(Type $collectedType)
    {
        $this->collectedType = $collectedType;
    }

    public function getCollectedType()
    {
        return $this->collectedType;
    }

    public function acceptTypeVisitor(TypeVisitor $visitor)
    {
        // visit this, there are no children to pass visitor on to
        $visitor->visitCollectionType($this);
    }

    public function getReferencedTypeIfAny()
    {
        return $this->collectedType->getReferencedTypeIfAny();
    }
}

?>
