<?php

namespace Good\Rolemodel\Schema;

use Good\Rolemodel\TypeVisitor;

interface Type
{
    public function acceptTypeVisitor(TypeVisitor $visitor);
}

?>
