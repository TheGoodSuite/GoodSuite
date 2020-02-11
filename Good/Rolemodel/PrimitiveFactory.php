<?php

namespace Good\Rolemodel;

class PrimitiveFactory
{
    public function makePrimitive(array $attributes, $name, $type, $typeModifiers)
    {
        switch ($type)
        {
            case 'text':
                return new Schema\TextMember($attributes, $name, $typeModifiers);

            case 'int':
                return new Schema\IntMember($attributes, $name, $typeModifiers);

            case 'float';
                return new Schema\FloatMember($attributes, $name, $typeModifiers);

            case 'datetime';
                return new Schema\DatetimeMember($attributes, $name, $typeModifiers);

            default:
                // TODO: better error handling
                throw new \Exception("Unrecognized type.");
        }
    }
}

?>
