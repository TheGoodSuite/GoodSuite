<?php

namespace Good\Rolemodel;

abstract class TypePrimitive extends Type
{
	public function getReferencedTypeIfAny()
	{
		return null;
	}
}

?>