<?php

namespace Good\Memory;

class StorableCollection implements \Good\Manners\StorableCollection
{
    protected $storage;
    protected $dbresult;
    private $joins;
    private $type;
    
    private $firstStorable;
    private $lastStorable;
    
    private $reachedEnd;

    public function __construct($storage, $dbresult, $joins, $type)
    {
        $this->storage = $storage;
        $this->dbresult = $dbresult;
        $this->joins = $joins;
        $this->type = $type;
        
        $this->firstStorable = new LinkedListElement();
        $this->lastStorable = $this->firstStorable;
        $this->reachedEnd = false;
    }
    
    public function getNext()
    {
        $ret = $this->lastStorable;
    
        if ($this->moveNext())
        {
            return $ret->value;
        }
        else
        {
            return null;
        }
    }
    
    public function moveNext()
    {
        $row = $this->dbresult->fetch();
        
        if ($row !== null && !$this->reachedEnd)
        {
            $this->lastStorable->value = $this->storage->createStorable($row, $this->joins, $this->type);
            $this->lastStorable->next = new LinkedListElement();
            $this->lastStorable = $this->lastStorable->next;
            
            return true;
        }
        else
        {
            $this->reachedEnd = true;
            return false;
        }
    }
    
    public function getIterator()
    {
        if ($this->firstStorable->value == null)
        {
            $this->moveNext();
        }
        
        return new StorableCollectionIterator($this, $this->firstStorable);
    }
}

?>