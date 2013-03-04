<?php

namespace Good\Rolemodel;

include_once 'Type.php';

abstract class TypePrimitive extends Type
{
	public function getReferencedTypeIfAny()
	{
		return null;
	}
}

?>