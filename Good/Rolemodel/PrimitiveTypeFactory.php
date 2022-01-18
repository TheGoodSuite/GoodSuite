<?php

namespace Good\Rolemodel;

class PrimitiveTypeFactory
{
    public function makePrimitiveType($type, $typeModifiers, $memberName)
    {
        switch ($type)
        {
            case 'text':
                return new Schema\Type\TextType($typeModifiers, $memberName);

            case 'int':
                return new Schema\Type\IntType($typeModifiers, $memberName);

            case 'float';
                return new Schema\Type\FloatType($typeModifiers, $memberName);

            case 'datetime';
                return new Schema\Type\DateTimeType($typeModifiers, $memberName);

            default:
                // TODO: better error handling
                throw new \Exception("Unrecognized type.");
        }
    }
}

?>
