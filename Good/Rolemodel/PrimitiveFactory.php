<?php

namespace Good\Rolemodel;

include_once 'TypePrimitiveText.php';
include_once 'TypePrimitiveInt.php';
include_once 'TypePrimitiveFloat.php';
include_once 'TypePrimitiveDatetime.php';

class PrimitiveFactory
{
	public static function makePrimitive($value)
	{
		switch ($value)
		{
			case 'text':
				return new TypePrimitiveText();
			
			case 'int':
				return new TypePrimitiveInt();
			
			case 'float';
				return new TypePrimitiveFloat();
			
			case 'datetime';
				return new TypePrimitiveDatetime();
				
			default:
				// TODO: better error handling
				die("Unrecognized type.");
		}
	}
}

?>