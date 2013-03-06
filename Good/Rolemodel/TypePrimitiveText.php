<?php

namespace Good\Rolemodel;

class TypePrimitiveText extends TypePrimitive
{
	public function accept(Visitor $visitor)
	{
		// visit this, there are no children to pass visitor on to
		$visitor->visitTypePrimitiveText($this);
	}
}

?>