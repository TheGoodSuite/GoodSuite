<?php

include_once 'Type.php';

abstract class GoodRolemodelTypePrimitive extends GoodRolemodelType
{
	public function getReferencedTypeIfAny()
	{
		return null;
	}
}

?>