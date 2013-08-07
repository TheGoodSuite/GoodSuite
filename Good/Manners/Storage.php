<?php

namespace Good\Manners;

abstract class Storage
{
    private $validationToken;
    
    public function __construct()
    {
        $this->validationToken = new ValidationToken();
    }
    
    public function __destruct()
    {
        $this->flush();
    }
    
    protected function invalidate()
    {
        $this->validationToken->invalidate();
        $this->validationToken = new ValidationToken();
    }
    
    abstract public function insert(Storable $storable);
    abstract public function modifyAny(Condition $condition, Storable $modifications);
    abstract public function getCollection(Condition $condition, Resolver $resolver);
    
    abstract public function flush();
    
    abstract public function dirtyStorable(Storable $storable);
}

?>