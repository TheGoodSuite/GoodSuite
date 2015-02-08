<?php

namespace Good\Service;

use Good\Service\InvalidParameterException;

class TypeChecker
{
    public static function checkInt($value)
    {
        if ($value != null && !is_int($value))
        {
            throw new InvalidParameterException("Value specified is not an int");
        }
    }
    
    public static function checkFloat($value)
    {
        if ($value != null && !is_float($value))
        {
            throw new InvalidParameterException("Value specified is not a float");
        }
    }
    
    public static function checkString($value)
    {
        if ($value != null && !is_string($value))
        {
            throw new InvalidParameterException("Value specified is not a string");
        }
    }
    
    public static function checkDateTime($value)
    {
        if ($value != null && !($value instanceof \DateTime))
        {
            throw new InvalidParameterException("Value specified is not a DateTime");
        }
    }
}

?>