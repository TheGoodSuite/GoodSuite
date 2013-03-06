<?php

namespace Good\Rolemodel;

class TypePrimitiveInt extends TypePrimitive
{
	public function accept(Visitor $visitor)
	{
		// visit this, there are no children to pass visitor on to
		$visitor->visitTypePrimitiveInt($this);
	}
}

?>