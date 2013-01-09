<?php

include_once 'TypePrimitive.php';

class GoodRolemodelTypePrimitiveDatetime extends GoodRolemodelTypePrimitive
{
	public function accept(GoodRolemodelVisitor $visitor)
	{
		// visit this, there are no children to pass visitor on to
		$visitor->visitTypePrimitiveDatetime($this);
	}
}

?>