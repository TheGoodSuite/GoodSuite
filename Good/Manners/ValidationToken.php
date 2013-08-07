<?php

namespace Good\Manners;

class ValidationToken
{
    private $value;
    
    public function __construct()
    {
        $this->value = true;
    }
    
    public function invalidate()
    {
        $this->value = false;
    }
    
    public function value()
    {
        return $this->value;
    }
}