<?php

include_once 'TypePrimitiveText.php';
include_once 'TypePrimitiveInt.php';
include_once 'TypePrimitiveFloat.php';
include_once 'TypePrimitiveDatetime.php';

class GoodRolemodelPrimitiveFactory
{
	public static function makePrimitive($value)
	{
		switch ($value)
		{
			case 'text':
				return new GoodRolemodelTypePrimitiveText();
			
			case 'int':
				return new GoodRolemodelTypePrimitiveInt();
			
			case 'float';
				return new GoodRolemodelTypePrimitiveFloat();
			
			case 'datetime';
				return new GoodRolemodelTypePrimitiveDatetime();
				
			default:
				// TODO: better error handling
				die("Unrecognized type.");
		}
	}
}

?>