<?php

namespace Good\Manners;

use Good\Manners\Storable;
use Good\Manners\Storage;

class Id implements Storable
{
    private $base;
    private $id;
    
    private $storage = null;
    private $deleted = false;
    
    public function __construct(Storable $base, $id)
    {
        $this->base = $base;
        $this->id = $id;
    }
    
    public function setStorage(Storage $storage)
    {
        $this->storage = $storage;
    }
    
    public function delete()
    {
        if ($this->storage === null)
        {
            throw new \Exception("Can't delete by id unless a store is set on id");
        }
        
        $this->deleted = true;
        $this->storage->dirtyStorable($this);
    }
    
    public function isDeleted()
    {
        return $this->deleted;
    }
    
    public function isDirty()
    {
        // Only way to make an id dirty is to delete it
        return $this->deleted;
    }
    
    public function setNew($value)
    {
    }
    
    public function isNew()
    {
        return false;
    }
    
    public function setValidationToken(ValidationToken $token)
    {
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($value)
    {
        // Does this function even make sense?
        $this->id = id;
    }
    
    public function hasValidId()
    {
        return true;
    }
    
    public function clean()
    {
        // ?
    }
    
    public function getType()
    {
        return $this->base->getType();
    }
    
    public function acceptStorableVisitor(StorableVisitor $visitor)
    {
        // This doesn't need to do anything, because we don't care about the fields of an id
    }
    
    public function setFromArray(array $values)
    {
        throw new Exception("You can't set values on an Id.");
    }
    
    public static function resolver()
    {
        throw new Exception("You can't get a resolver from an Id.");
    }
}

?>