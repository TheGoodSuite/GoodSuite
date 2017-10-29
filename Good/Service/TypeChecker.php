<?php

namespace Good\Service;

use Good\Service\InvalidParameterException;

class TypeChecker
{
    public static function checkInt($value, $minValue, $maxValue)
    {
        if ($value === null)
        {
            return;
        }
        
        if (!is_int($value))
        {
            throw new InvalidParameterException("Value specified is not an int");
        }
        
        if ($value < $minValue)
        {
            throw new InvalidParameterException("Value specified is below the minimum value for this field");
        }
        
        if ($value > $maxValue)
        {
            throw new InvalidParameterException("Value specified is above the maximum value for this field");
        }
    }
    
    public static function checkFloat($value)
    {
        if ($value === null)
        {
            return;
        }
        
        if (!is_int($value) && !is_float($value))
        {
            throw new InvalidParameterException("Value specified is not a float");
        }
    }
    
    public static function checkString($value, $minLength, $maxLength = null)
    {
        if ($value === null)
        {
            return;
        }
        
        if (!is_string($value))
        {
            throw new InvalidParameterException("Value specified is not a string");
        }
        
        if (strlen($value) < $minLength)
        {
            throw new InvalidParameterException("Value specified is shorter than the minimum length for this field");
        }
        
        if ($maxLength !== null && strlen($value) > $maxLength)
        {
            throw new InvalidParameterException("Value specified is longer the maximum length for this field");
        }
    }
    
    public static function checkDateTime($value)
    {
        if ($value === null)
        {
            return;
        }
        
        if (!($value instanceof \DateTime))
        {
            throw new InvalidParameterException("Value specified is not a DateTime");
        }
    }
}

?>