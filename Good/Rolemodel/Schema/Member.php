<?php

namespace Good\Rolemodel\Schema;

use Good\Rolemodel\VisitableSchema;

class Member
{
    private $attributes;
    private $name;
    private $type;

    private static $knownAttributes = array('server_only', 'private', 'protected', 'public');

    public function __construct(array $attributes, $name, $type)
    {
        $this->attributes = $attributes;
        $this->type = $type;

        // check for unknown attributes
        foreach ($attributes as $attribute)
        {
            if (!\in_array($attribute, self::$knownAttributes))
            {
                // TODO: add a real warning

                // WARNING: unknown attribute
            }
        }

        // Name
        $this->name = $name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getName()
    {
        return $this->name;
    }
}

?>
