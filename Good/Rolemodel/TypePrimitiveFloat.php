<?php

namespace Good\Rolemodel;

class TypePrimitiveFloat extends TypePrimitive
{
	public function accept(Visitor $visitor)
	{
		// visit this, there are no children to pass visitor on to
		$visitor->visitTypePrimitiveFloat($this);
	}
}

?>