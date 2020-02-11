<?php

namespace Good\Rolemodel\Schema\Type;

use Good\Rolemodel\Schema\Type;
use Good\Rolemodel\TypeVisitor;

class ReferenceType implements Type
{
    private $referencedType;

    public function __construct($referencedType)
    {
        $this->referencedType = $referencedType;
    }

    public function acceptTypeVisitor(TypeVisitor $visitor)
    {
        // visit this, there are no children to pass visitor on to
        $visitor->visitReferenceType($this);
    }

    public function getReferencedType()
    {
        return $this->referencedType;
    }

    public function getReferencedTypeIfAny()
    {
        return $this->referencedType;
    }
}

?>
