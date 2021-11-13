<?php

namespace Good\Service\Type;

use Good\Service\Type;

class ReferenceType extends \Good\Rolemodel\Schema\Type\ReferenceType implements Type
{
    public function checkValue($value)
    {
        // php is weird and doesn't allow inlining this following method call...
        $type = $this->getReferencedType();

        if (!($value instanceof $type)
            && !is_null($value))
        {
            return "must be of type " . $this->getReferencedType();
        }

        return null;
    }
}

?>
