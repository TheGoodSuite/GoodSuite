<?php

namespace Good\Rolemodel\Schema;

use Good\Rolemodel\Visitor;

class TextMember extends PrimitiveMember
{
    public function accept(Visitor $visitor)
    {
        // visit this, there are no children to pass visitor on to
        $visitor->visitTextMember($this);
    }
}

?>