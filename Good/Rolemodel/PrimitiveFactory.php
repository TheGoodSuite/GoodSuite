<?php

namespace Good\Rolemodel;

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