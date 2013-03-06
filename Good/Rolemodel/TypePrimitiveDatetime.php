<?php

namespace Good\Rolemodel;

class TypePrimitiveDatetime extends TypePrimitive
{
	public function accept(Visitor $visitor)
	{
		// visit this, there are no children to pass visitor on to
		$visitor->visitTypePrimitiveDatetime($this);
	}
}

?>