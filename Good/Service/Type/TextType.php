<?php

namespace Good\Service\Type;

use Good\Service\Type;
use Good\Service\InvalidParameterException;

class TextType extends \Good\Rolemodel\Schema\Type\TextType implements Type
{
    public function checkValue($value)
    {
        if ($value === null)
        {
            return;
        }

        if (!is_string($value))
        {
            throw new InvalidParameterException("Value specified is not a string");
        }

        if (strlen($value) < $this->getTypeModifiers()['minLength'])
        {
            throw new InvalidParameterException("Value specified is shorter than the minimum length for this field");
        }

        if (array_key_exists('maxLength', $this->getTypeModifiers()) && strlen($value) > $this->getTypeModifiers()['maxLength'])
        {
            throw new InvalidParameterException("Value specified is longer the maximum length for this field");
        }
    }
}

?>
