<?php

namespace Good\Rolemodel\Schema;

use Good\Rolemodel\Visitor;

class FloatMember extends PrimitiveMember
{
	public function accept(Visitor $visitor)
	{
		// visit this, there are no children to pass visitor on to
		$visitor->visitFloatMember($this);
	}
}

?>