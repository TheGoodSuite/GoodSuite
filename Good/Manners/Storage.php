<?php

namespace Good\Manners;

abstract class Storage
{
    private $validationToken;
    
    protected $storableFactory;
    private $defaultStorableFactory;
    
    public function __construct()
    {
        $this->validationToken = new ValidationToken();
        
        $this->defaultStorableFactory = new DefaultStorableFactory();
        $this->storableFactory = $this->defaultStorableFactory;
    }
    
    public function __destruct()
    {
        $this->flush();
    }
    
    public function setStorableFactory(StorableFactory $factory)
    {
        $this->storableFactory = $factory;
    }
    
    public function registerType($parentType, $childType)
    {
        if ($this->storableFactory != $this->defaultStorableFactory)
        {
            throw new Exception("Tried to register a type to the StorableFactory after " .
                                    "it was exchanged set to something else than the default.");
        }
        
        $this->defaultStorableFactory->registerType($parentType, $childType);
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