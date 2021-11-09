<?php

namespace Good\Service\Type;

use Good\Service\Type;

class TextType extends \Good\Rolemodel\Schema\Type\TextType implements Type
{
    public function checkValue($value)
    {
        if ($value === null)
        {
            return null;
        }

        if (!is_string($value))
        {
            return "must a string";
        }

        if (strlen($value) < $this->getTypeModifiers()['minLength'])
        {
            return "shorter than the minimum length for this field";
        }

        if (array_key_exists('maxLength', $this->getTypeModifiers()) && strlen($value) > $this->getTypeModifiers()['maxLength'])
        {
            return "longer the maximum length for this field";
        }

        return null;
    }
}

?>
