<?php

namespace Good\Rolemodel;

class PrimitiveFactory
{
    public function makePrimitive(array $attributes, $name, $type)
    {
        switch ($type)
        {
            case 'text':
                return new Schema\TextMember($attributes, $name);
            
            case 'int':
                return new Schema\IntMember($attributes, $name);
            
            case 'float';
                return new Schema\FloatMember($attributes, $name);
            
            case 'datetime';
                return new Schema\DatetimeMember($attributes, $name);
                
            default:
                // TODO: better error handling
                throw new \Exception("Unrecognized type.");
        }
    }
}

?>