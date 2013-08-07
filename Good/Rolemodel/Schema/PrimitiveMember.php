<?php

namespace Good\Rolemodel\Schema;

abstract class PrimitiveMember extends Member
{
    public function getReferencedTypeIfAny()
    {
        return null;
    }
}

?>